<?php

namespace Orchid\Entity;

use RuntimeException;
use Orchid\App;

final class Daemon {
	public static $name = "daemon";

	protected static $pid     = null;
	protected static $pidFile = null;
	protected static $log     = null;
	protected static $logErr  = null;

	/**
	 * Запускает выполнение демона
	 *
	 * @param string $daemon
	 * @param array  $args
	 * @param string $path
	 *
	 * @return string
	 */
	public static function run($daemon, array $args = [], $path = "/daemon/") {
		return system("php " . App::getBaseDir() . $path . $daemon . ".php " . implode(" ", $args) . " > /dev/null &");
	}

	/**
	 * Отключает рабочий процесс от консоли, создавая дочерний процесс
	 * Внимание: при использовании данного метода, возможен запуск лишь одного инстанса
	 *
	 * @throws RuntimeException
	 */
	public static function forkProcess() {
		if (PHP_SAPI == "cli") {
			if (!pcntl_fork()) {
				posix_setsid();
				static::$pid = getmypid();
				static::$pidFile = App::getBaseDir() . "/storage/cache/" . static::$name . ".pid";

				if (!file_exists(static::$pidFile)) {
					// запись pid в файл
					file_put_contents(static::$pidFile, static::$pid);

					// обработка завершения работы демона
					register_shutdown_function(function () {
						unlink(Daemon::$pidFile);
					});
				} else {
					echo "Daemon already running!" . PHP_EOL;
					exit(SIG_ERR);
				}
			} else {
				exit(SIG_DFL);
			}
		} else {
			throw new RuntimeException("Данный метод доступен только в Daemon");
		}
	}

	/**
	 * Переключает процесс на запись output в лог файл
	 *
	 * @throws RuntimeException
	 */
	public static function writeLog() {
		if (PHP_SAPI == "cli") {
			static::$log = App::getBaseDir() . "/storage/cache/" . static::$name . ".log";
			static::$logErr = App::getBaseDir() . "/storage/cache/" . static::$name . "-error.log";

			fclose(STDIN);
			$GLOBALS["STDIN"] = fopen("/dev/null", "r");

			fclose(STDOUT);
			$GLOBALS["STDOUT"] = fopen(static::$log, "ab");

			fclose(STDERR);
			$GLOBALS["STDERR"] = fopen(static::$logErr, "ab");
		} else {
			throw new RuntimeException("Данный метод доступен только в Daemon");
		}
	}
}