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

namespace Engine\Entity;

abstract class Daemon extends AppAware {
	protected $pid      = null;
	protected $pid_file = null;

	public final function __construct() {
		parent::__construct();

		if (!pcntl_fork()) {
			posix_setsid();
			$this->pid      = getmypid();
			$this->pid_file = $this->app["base_dir"] . "/Cache/" . $this->app["args"][0] . ".pid";

			if (!file_exists($this->pid_file)) {
				$this->initialize();

				file_put_contents($this->pid_file, $this->pid);

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
		// обработка сигналов
		pcntl_signal(SIGTERM, [$this, "sigHandler"]);
		pcntl_signal_dispatch();
	}

	/**
	 * Обработчик сигналов
	 * @param $signo
	 * @return void
	 */
	public function sigHandler($signo) {
		switch ($signo) {
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