<?php

namespace Orchid\Classes\Driver\Cache;

use Orchid\App;

class Memcache {
	protected $connection = null;

	/**
	 * Создаёт подключение к серверу внешнего хранилища
	 * @param string $host
	 * @param int    $port
	 * @param int    $timeout
	 */
	public function __construct($host, $port, $timeout) {
		$this->connection = new \Memcache;

		if (!$this->connection->connect($host, $port, $timeout)) {
			http_response_code(500);
			App::terminate("Подключение к кеширующему серверу не удалось");
		}
	}

	/**
	 * Производит чтение ключа из внешнего хранилища и возвращает значение
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->connection->get($key);
	}

	/**
	 * Записывает значение для ключа во внешнее хранилище
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expire
	 * @return bool
	 */
	public function set($key, $value, $expire = 0) {
		return $this->connection->set($key, $value, MEMCACHE_COMPRESSED, $expire);
	}

	/**
	 * Удаляет указанный ключ из внешнего хранилища
	 * @param string $key
	 * @return bool
	 */
	public function delete($key) {
		return $this->connection->delete($key);
	}

	/**
	 * Удаляет все ключи из внешнего хранилища
	 * @return bool
	 */
	public function flush() {
		return $this->connection->flush();
	}
}