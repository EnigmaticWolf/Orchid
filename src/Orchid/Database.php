<?php

namespace AEngine\Orchid;

use AEngine\Orchid\Entity\Exception\DatabaseException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var array
     */
    protected $connection = [
        'master' => [],
        'slave'  => [],
    ];

    /**
     * @var PDO
     */
    protected $lastConnection = null;

    /**
     * Database constructor
     *
     * @param App   $app
     * @param array $configs
     *
     * @throws RuntimeException
     * @throws DatabaseException
     */
    public function __construct(App $app, array $configs)
    {
        $this->app = $app;

        if ($configs) {
            $default = [
                'dsn'      => '',
                'username' => '',
                'password' => '',
                'options'  => [],
                'role'     => 'master',
            ];

            $keyHash = 'database:' . spl_object_hash($this) . ':';
            foreach ($configs as $index => $config) {
                $key = $keyHash . $index;
                $config = array_merge($default, $config);

                $app->addClosure($key, function () use ($config) {
                    try {
                        return new PDO(
                            $config['dsn'],
                            $config['username'],
                            $config['password'],
                            $config['options']
                        );
                    } catch (PDOException $ex) {
                        throw new DatabaseException(
                            'The connection to the database server fails (' . $ex->getMessage() . ')',
                            0,
                            $ex
                        );
                    }
                });

                $this->connection[$config['role'] == 'master' ? 'master' : 'slave'][] = $key;
            }
        } else {
            throw new RuntimeException('There are no settings to connect to the database');
        }
    }

    /**
     * Prepares and executes a database query
     *
     * @param string $query
     * @param array  $params
     * @param bool   $use_master
     *
     * @return PDOStatement
     */
    public function query($query, array $params = [], $use_master = false)
    {
        // obtain connection
        $this->lastConnection = $this->getInstance(!$use_master ? !!strncmp($query, 'SELECT', 6) : true);

        $stm = $this->lastConnection->prepare($query);
        $stm->execute($params);

        return $stm;
    }

    /**
     * Returns PDO object
     *
     * @param bool $use_master
     *
     * @return PDO
     * @throws DatabaseException
     */
    public function getInstance($use_master = false)
    {
        $pool = [];
        $role = $use_master ? 'master' : 'slave';

        switch (true) {
            case !empty($this->connection[$role]):
                $pool = $this->connection[$role];
                break;
            case !empty($this->connection['master']):
                $pool = $this->connection['master'];
                $role = 'master';
                break;
            case !empty($this->connection['slave']):
                $pool = $this->connection['slave'];
                $role = 'slave';
                break;
        }

        if ($pool) {
            if (is_array($pool)) {
                return $this->connection[$role] = $this->app->getClosure($pool[array_rand($pool)]);
            } else {
                return $pool;
            }
        }

        throw new DatabaseException('Unable to establish connection');
    }

    /**
     * Returns the ID of the last inserted row
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->lastConnection->lastInsertId();
    }
}
