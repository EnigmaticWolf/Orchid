<?php

namespace Orchid\Entity\Driver\Cache;

use Orchid\Entity\Exception\CacheException;
use Memcached;

class Memcache {
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
	public function __construct($host, $port, $timeout) {
		$this->connection = new Memcached;
		$this->connection->setOption(Memcached::OPT_CONNECT_TIMEOUT, $timeout * 1000);
		$this->connection->addServer($host, $port);

		if (!$this->connection->getVersion()) {
			throw new CacheException("Connecting to a cache server was unable");
		}
	}

	/**
	 * Return value from external storage and returns
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->connection->get($key);
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
	public function set($key, $value, $expire = 0, $tag = null) {
		if ($tag) {
			$tags = $this->get($tag);
			$tags[] = $key;
			$this->set($tag, array_unique($tags));
		}

		return $this->connection->set($key, $value, $expire);
	}

	/**
	 * Removes specified key from the external storage
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function delete($key) {
		return $this->connection->delete($key);
	}

	/**
	 * Remove all keys from an external storage
	 *
	 * @return bool
	 */
	public function flush() {
		return $this->connection->flush();
	}

	/**
	 * Return values for a given tag
	 *
	 * @param string $tag
	 *
	 * @return array
	 */
	public function getByTag($tag) {
		$data = [];

		if (($tags = $this->get($tag)) !== false) {
			foreach ($tags as $key) {
				$data[$key] = $this->get($key);
			}
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
	public function deleteByTag($tag) {
		$deleted = 0;

		if (($tags = $this->get($tag)) !== false) {
			foreach ($tags as $key) {
				if ($this->delete($key)) {
					$deleted++;
				}
			}
			$this->delete($tag);
		}

		return !!$deleted;
	}
}