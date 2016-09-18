<?php

namespace Orchid\Entity\Driver\Cache;

use Memcached;
use Orchid\Entity\Exception\CacheException;

class Memcache
{
    protected $connection = null;

    /**
     * Create connection to the external storage server
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     *
     * @throws CacheException
     */
    public function __construct($host, $port, $timeout)
    {
        $this->connection = new Memcached;
        $this->connection->setOption(Memcached::OPT_CONNECT_TIMEOUT, $timeout * 1000);
        $this->connection->addServer($host, $port);

        if (!$this->connection->getVersion()) {
            throw new CacheException("Connecting to a cache server was unable");
        }
    }

    /**
     * Writes a value to an external storage key
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $expire
     * @param string $tag
     *
     * @return bool
     */
    public function set($key, $value, $expire = 0, $tag = null)
    {
        if ($tag) {
            $tags = $this->get($tag);
            $tags[] = $key;
            $this->set($tag, array_unique($tags));
        }

        return $this->connection->set($key, $value, $expire);
    }

    /**
     * Return value from external storage and returns
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->connection->get($key);
    }

    /**
     * Removes specified key from the external storage
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return $this->connection->delete($key);
    }

    /**
     * Remove all keys from an external storage
     *
     * @return bool
     */
    public function flush()
    {
        return $this->connection->flush();
    }

    /**
     * Return values for a given tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getByTag($tag)
    {
        $data = [];

        if (($keys = $this->get($tag)) !== false) {
            $this->connection->getMulti($keys);
        }

        return $data;
    }

    /**
     * Deletes values for a given tag
     *
     * @param string $tag
     *
     * @return bool
     */
    public function deleteByTag($tag)
    {
        if (($keys = $this->get($tag)) !== false) {
            $keys[] = $tag;

            return $this->connection->deleteMulti($keys);
        }

        return false;
    }
}
