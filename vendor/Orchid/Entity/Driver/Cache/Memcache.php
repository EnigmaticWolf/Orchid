<?php

namespace Orchid\Entity\Driver\Cache;

use Orchid\App;
use Orchid\Memory;

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
		return $this->connection->get(Memory::getKey($key));
	}

	/**
	 * Записывает значение для ключа во внешнее хранилище
	 * @param string      $key
	 * @param mixed       $value
	 * @param int         $expire
	 * @param string|null $tag
	 * @return bool
	 */
	public function set($key, $value, $expire = 0, $tag = null) {
		if ($tag) {
			$tags = $this->get($tag);
			$tags[] = $key;
			$this->set($tag, array_unique($tags));
		}

		return $this->connection->set(Memory::getKey($key), $value, MEMCACHE_COMPRESSED, $expire);
	}

	/**
	 * Удаляет указанный ключ из внешнего хранилища
	 * @param string $key
	 * @return bool
	 */
	public function delete($key) {
		return $this->connection->delete(Memory::getKey($key));
	}

	/**
	 * Удаляет все ключи из внешнего хранилища
	 * @return bool
	 */
	public function flush() {
		return $this->connection->flush();
	}

	/**
	 * Достаёт значения по указанному тегу
	 * @param string $tag
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
	 * Удаляет значения по указанному тегу
	 * @param string $tag
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