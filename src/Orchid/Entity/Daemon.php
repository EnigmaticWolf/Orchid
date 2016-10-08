<?php

namespace AEngine\Orchid\Entity;

use AEngine\Orchid\App;
use AEngine\Orchid\Entity\Exception\FileNotFoundException;
use RuntimeException;

class Daemon
{
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

    /**
     * Daemon constructor.
     *
     * @param string $name
     *
     * @throws RuntimeException
     */
    public function __construct($name = 'Daemon')
    {
        if (PHP_SAPI == 'cli') {
            $this->app = App::getInstance();
            $this->name = $name;
        } else {
            throw new RuntimeException('This method is available only in Daemon');
        }
    }

    /**
     * Starts the execution of a demon
     *
     * @param string $file
     * @param array  $args
     *
     * @return string
     * @throws FileNotFoundException
     */
    public static function run($file, array $args = [])
    {
        if (file_exists($file)) {
            return system('php ' . $file . ' ' . implode(' ', $args) . ' > /dev/null &');
        }

        throw new FileNotFoundException('This method is available only in Daemon');
    }

    /**
     * Disables the workflow from the console to create a child process
     * Note: when using this method, can run only one instance
     */
    public function forkProcess()
    {
        if (!pcntl_fork()) {
            posix_setsid();
            $this->pid = getmypid();
            $this->pidFile = $this->app->getBaseDir() . '/storage/log/' . $this->name . '.pid';

            if (!file_exists($this->pidFile)) {
                // write pid to file
                file_put_contents($this->pidFile, $this->pid);

                // daemon shutdown processing
                register_shutdown_function(function () {
                    unlink($this->pidFile);
                });
            } else {
                echo 'Daemon already running!' . PHP_EOL;
                exit(SIG_ERR);
            }
        } else {
            exit(SIG_DFL);
        }

        return $this;
    }

    /**
     * Switch to the output process entry in the log file
     */
    public function writeLog()
    {
        $this->log = $this->app->getBaseDir() . '/storage/log/' . $this->name . '.log';
        $this->logErr = $this->app->getBaseDir() . '/storage/log/' . $this->name . '-error.log';

        fclose(STDIN);
        $GLOBALS['STDIN'] = fopen('/dev/null', 'r');

        fclose(STDOUT);
        $GLOBALS['STDOUT'] = fopen($this->log, 'ab');

        fclose(STDERR);
        $GLOBALS['STDERR'] = fopen($this->logErr, 'ab');

        return $this;
    }
}
