<?php

use Orchid\Entity\Daemon;

require_once(__DIR__ . "/../bootstrap.php");

$daemon = new Daemon("Demo");

// share workflow
$daemon->forkProcess();

// write logs to a file
$daemon->writeLog();

for ($i = 0; $i < 10; $i++) {
    echo $i . " \n\r";
}
