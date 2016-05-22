<?php

namespace Orchid\Entity;

use RuntimeException;
use Orchid\App;

class Daemon {
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * Daemon name
	 *
	 * @var string
	 */
	protected $name;

	protected $pid;
	protected $pidFile;

	protected $log;
	protected $logErr;

	public function __construct(App $app, $name) {
		$this->app = $app;
		$this->name = $name;
	}

	/**
	 * Starts the execution of a demon
	 *
	 * @param string $daemon
	 * @param array  $args
	 * @param string $path
	 *
	 * @return string
	 */
	public function run($daemon, array $args = [], $path = "/daemon/") {
		return system("php " . $this->app->getBaseDir() . $path . $daemon . ".php " . implode(" ", $args) . " > /dev/null &");
	}

	/**
	 * Disables the workflow from the console to create a child process
	 * Note: when using this method, can run only one instance
	 *
	 * @throws RuntimeException
	 */
	public function forkProcess() {
		if (PHP_SAPI == "cli") {
			if (!pcntl_fork()) {
				posix_setsid();
				$this->pid = getmypid();
				$this->pidFile = $this->app->getBaseDir() . "/storage/log/" . $this->name . ".pid";

				if (!file_exists($this->pidFile)) {
					// write pid to file
					file_put_contents($this->pidFile, $this->pid);

					// daemon shutdown processing
					register_shutdown_function(function () {
						unlink($this->pidFile);
					});
				} else {
					echo "Daemon already running!" . PHP_EOL;
					exit(SIG_ERR);
				}
			} else {
				exit(SIG_DFL);
			}
		} else {
			throw new RuntimeException("This method is available only in Daemon");
		}
	}

	/**
	 * Switch to the output process entry in the log file
	 *
	 * @throws RuntimeException
	 */
	public function writeLog() {
		if (PHP_SAPI == "cli") {
			$this->log = $this->app->getBaseDir() . "/storage/log/" . $this->name . ".log";
			$this->logErr = $this->app->getBaseDir() . "/storage/log/" . $this->name . "-error.log";

			fclose(STDIN);
			$GLOBALS["STDIN"] = fopen("/dev/null", "r");

			fclose(STDOUT);
			$GLOBALS["STDOUT"] = fopen($this->log, "ab");

			fclose(STDERR);
			$GLOBALS["STDERR"] = fopen($this->logErr, "ab");
		} else {
			throw new RuntimeException("This method is available only in Daemon");
		}
	}
}