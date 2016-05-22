<?php

namespace Orchid;

use RuntimeException;
use Orchid\Entity\Driver\Cache\Memcache;
use Orchid\Entity\Exception\CacheException;

class Memory {
	/**
	 * Prevents reading from external storage
	 *
	 * @var bool
	 */
	public $disabled = false;

	/**
	 * Prefix keys
	 *
	 * @var string
	 */
	public $prefix = "";

	/**
	 * List of keys that are stored in the buffer
	 *
	 * @var array
	 */
	public $cachedKeys = [];

	/**
	 * Internal storage
	 *
	 * @var array
	 */
	protected $buffer = [];

	/**
	 * Array of connections with external storage
	 *
	 * @var array
	 */
	protected $instance = [];

	/**
	 * Memory constructor
	 *
	 * @param App   $app
	 * @param array $configs
	 *
	 * @throws RuntimeException
	 * @throws CacheException
	 */
	public function __construct(App $app, array $configs) {
		$this->app = $app;

		if ($configs) {
			$default = [
				"driver"  => "memcache",
				"host"    => "",
				"port"    => "",
				"timeout" => 10,
				"role"    => "master",
			];

			$keyHash = "memory:" . spl_object_hash($this) . ":";
			foreach ($configs as $index => $config) {
				$key = $keyHash . $index;
				$config = array_merge($default, $config);

				switch (strtolower($config["driver"])) {
					case "memcache": {
						$app->addClosure($key, function () use ($config) {
							return new Memcache(
								$config["host"],
								$config["port"],
								$config["timeout"]
							);
						});

						break;
					}
				}

				$this->instance[strtolower($config["role"]) == "master" ? "master" : "slave"][] = $key;
			}
		} else {
			throw new RuntimeException("There are no settings to connect to the memory");
		}
	}

	/**
	 * Opens and returns a connection to external storage
	 *
	 * @param bool $use_master
	 *
	 * @return Memcache
	 * @throws CacheException
	 */
	public function getInstance($use_master = false) {
		$pool = [];
		$role = $use_master ? "master" : "slave";

		switch (true) {
			case !empty($this->instance[$role]): {
				$pool = $this->instance[$role];
				break;
			}
			case !empty($this->instance["master"]): {
				$pool = $this->instance["master"];
				$role = "master";
				break;
			}
			case !empty($this->instance["slave"]): {
				$pool = $this->instance["slave"];
				$role = "slave";
				break;
			}
		}

		if ($pool) {
			if (is_array($pool)) {
				return $this->instance[$role] = $this->app->getClosure($pool[array_rand($pool)]);
			} else {
				return $pool;
			}
		}

		throw new CacheException("Unable to establish connection");
	}

	/**
	 * Generate key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getKey($key) {
		return $this->prefix ? $this->prefix . ":" . $key : $key;
	}

	/**
	 * Return value from external storage
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = false) {
		if (!$this->disabled) {
			if (isset($this->buffer[$key])) {
				$value = $this->buffer[$key];
			} else {
				$value = $this->getInstance(false)->get($this->getKey($key));

				foreach ($this->cachedKeys as $k) {
					if (strpos($key, $k) === 0) {
						$this->buffer[$key] = $value;
					}
				}
			}

			return $value !== false ? $value : $default;
		}

		return $default;
	}

	/**
	 * Writes a value to external storage
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expire
	 * @param string $tag
	 *
	 * @return bool
	 */
	public function set($key, $value, $expire = 0, $tag = null) {
		if (isset($this->cachedKeys[$key])) {
			unset($this->buffer[$key]);
		}

		return $this->getInstance(true)->set($this->getKey($key), $value, $expire, $tag);
	}

	/**
	 * Removes specified key from external storage
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function delete($key) {
		if (isset($this->cachedKeys[$key])) {
			unset($this->buffer[$key]);
		}

		return $this->getInstance(true)->delete($this->getKey($key));
	}

	/**
	 * Remove all keys from external storage
	 *
	 * @return bool
	 */
	public function flush() {
		$this->buffer = [];

		return $this->getInstance(true)->flush();
	}

	/**
	 * Return values for a given tag
	 *
	 * @param string $tag
	 *
	 * @return array
	 */
	public function getByTag($tag) {
		return $this->getInstance(false)->getByTag($this->getKey($tag));
	}

	/**
	 * Deletes values for a given tag
	 *
	 * @param string $tag
	 *
	 * @return bool
	 */
	public function deleteByTag($tag) {
		return $this->getInstance(true)->deleteByTag($this->getKey($tag));
	}
}