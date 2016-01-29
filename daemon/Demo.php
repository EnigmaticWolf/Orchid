<?php

// подключаем файл начальной загрузки
require_once(__DIR__ . "/../bootstrap.php");

use Orchid\Classes\Daemon;

// разделяем рабочий процесс
Daemon::forkProcess("Demo");

// пишем логи в файл
Daemon::writeLog("Demo");

for($i = 0; $i < 10; $i++){
	echo $i . " \n\r";
}