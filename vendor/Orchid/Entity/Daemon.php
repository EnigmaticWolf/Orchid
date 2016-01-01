<?php
/*
 * Copyright (c) 2011-2016 AEngine
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Orchid\Entity;

use Orchid\App;

abstract class Daemon extends AppAware {
	protected $is_daemon = true; // флаг переключающий режимы daemon/bin

	protected $pid       = null;
	protected $pid_file  = null;
	protected $log       = null;
	protected $log_err   = null;

	public final function __construct() {
		// если демон, то переопределяем вывод в файл
		if ($this->is_daemon) {
			fclose(STDIN);
			fclose(STDOUT);
			fclose(STDERR);

			$this->log     = App::get("base_dir") . "/cache/" . App::retrieve("args/0", "daemon") . ".log";
			$this->log_err = App::get("base_dir") . "/cache/" . App::retrieve("args/0", "daemon") . "-error.log";

			$STDIN	= fopen("/dev/null", "r");
			$STDOUT = fopen($this->log, 'ab');
			$STDERR = fopen($this->log_err, 'ab');
		}

		if (!pcntl_fork()) {
			posix_setsid();
			$this->pid      = getmypid();
			$this->pid_file = App::get("base_dir") . "/Cache/" . App::retrieve("args/0", "daemon") . ".pid";

			if (!file_exists($this->pid_file)) {
				// если демон, то выполняем функцию инициализации
				if ($this->is_daemon) {
					$this->initialize();
				}

				$this->run();
			} else {
				echo "Daemon already running!" . PHP_EOL;
				exit(SIG_ERR);
			}
		} else {
			exit(SIG_DFL);
		}
	}

	/**
	 * Инициализация демона
	 * @return void
	 */
	protected function initialize() {
		// запись pid в файл
		file_put_contents($this->pid_file, $this->pid);

		// обработка сигналов
		pcntl_signal(SIGTERM, [$this, "sigHandler"]);
		pcntl_signal_dispatch();
	}

	/**
	 * Обработчик сигналов
	 * @param $sigNo
	 * @return void
	 */
	public function sigHandler($sigNo) {
		switch ($sigNo) {
			case SIGTERM: {
				exit(SIGTERM);
				break;
			}
		}
	}

	/**
	 * Рабочий код демона
	 * @return void
	 */
	abstract public function run();

	/**
	 * Обработка завершения работы демона
	 */
	public final function __destruct() {
		if (file_exists($this->pid_file) && file_get_contents($this->pid_file) == getmypid()) {
			unlink($this->pid_file);
		}
	}
}