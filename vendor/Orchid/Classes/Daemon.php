<?php

namespace Orchid\Classes;

use Orchid\App;

final class Daemon {
	public static $pid     = null;
	public static $pidFile = null;
	public static $log     = null;
	public static $logErr  = null;

	/**
	 * Запускает выполнение демона
	 * @param string $daemon
	 * @param array  $args
	 * @param string $path
	 * @return string
	 */
	public static function run($daemon, array $args = [], $path = "/daemon/") {
		return system("php " . App::get("base_dir") . $path . $daemon . ".php " . implode(" ", $args) . " > /dev/null &");
	}

	/**
	 * Отключает рабочий процесс от консоли, создавая дочерний процесс
	 * Внимание: при использовании данного метода, возможен запуск лишь одного инстанса
	 * @param string $fileName
	 * @return void
	 */
	public static function forkProcess($fileName = "daemon") {
		if (!pcntl_fork()) {
			posix_setsid();
			static::$pid     = getmypid();
			static::$pidFile = App::get("base_dir") . "/cache/" . $fileName . ".pid";

			if (!file_exists(static::$pidFile)) {
				// запись pid в файл
				file_put_contents(static::$pidFile, static::$pid);

				// обработка завершения работы демона
				register_shutdown_function(function () {
					if (file_exists(Daemon::$pidFile)) {
						unlink(Daemon::$pidFile);
					}
				});
			} else {
				echo "Daemon already running!" . PHP_EOL;
				exit(SIG_ERR);
			}
		} else {
			exit(SIG_DFL);
		}
	}

	/**
	 * Переключает процесс на запись output в лог файл
	 * @param string $fileName
	 * @return void
	 */
	public static function writeLog($fileName = "daemon") {
		static::$log    = App::get("base_dir") . "/cache/" . $fileName . ".log";
		static::$logErr = App::get("base_dir") . "/cache/" . $fileName . "-error.log";

		fclose(STDIN);
		$GLOBALS["STDIN"] = fopen("/dev/null", "r");

		fclose(STDOUT);
		$GLOBALS["STDOUT"] = fopen(static::$log, "ab");

		fclose(STDERR);
		$GLOBALS["STDERR"] = fopen(static::$logErr, "ab");
	}
}