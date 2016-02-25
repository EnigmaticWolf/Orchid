<?php

// подключаем файл начальной загрузки
require_once(__DIR__ . "/../bootstrap.php");

use Orchid\Entity\Daemon;

Daemon::$name = "Demo";

// разделяем рабочий процесс
Daemon::forkProcess();

// пишем логи в файл
Daemon::writeLog();

for($i = 0; $i < 10; $i++){
	echo $i . " \n\r";
}