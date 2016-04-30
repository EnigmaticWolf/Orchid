<?php

namespace Orchid\Entity;

use Orchid\App;
use Orchid\Entity\Exception\FileNotFoundException;

class View {
	/**
	 * Адрес файла с общим шаблоном
	 *
	 * @var string
	 */
	public static $layout = "view:layout.php";

	/**
	 * Адрес файла шаблона
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Массив данных передваваемых в шаблон
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Массив глобальных данных передваваемых во все шаблоны
	 *
	 * @var array
	 */
	protected static $globalData = [];

	/**
	 * Задаёт глобальные переменные для всех View
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setGlobal($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				View::$globalData[$k] = $v;
			}
		} else {
			View::$globalData[$key] = $value;
		}
	}

	/**
	 * Создаёт объект View
	 *
	 * @param string $file
	 * @param array  $data
	 */
	protected function __construct($file, array $data = []) {
		$this->file = $file;
		$this->data = $data;
	}

	/**
	 * Возвращает новый объект View
	 *
	 * @param string $file
	 * @param array  $data
	 *
	 * @return View
	 */
	public static function create($file, array $data = []) {
		return new View($file, $data);
	}

	/**
	 * Задаёт переменные для текущего View
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->data[$k] = $v;
			}
		} else {
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Отрисовывает объект View
	 *
	 * @return string
	 */
	public function render() {
		if (!empty($this->file)) {
			$this->data["content"] = View::fetch($this->file, $this->data);
		}

		return View::fetch(static::$layout, $this->data);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * @param string $_file
	 * @param array  $_data
	 *
	 * @return bool
	 * @throws FileNotFoundException
	 */
	public static function fetch($_file, array $_data = []) {
		if ($_data) {
			extract($_data, EXTR_SKIP);
		}

		if (View::$globalData) {
			extract(View::$globalData, EXTR_SKIP);
		}

		if ($_file = App::getPath($_file)) {
			ob_start();
			require $_file;

			return ob_get_clean();
		}

		throw new FileNotFoundException("Не удалось найти файл шаблона");
	}
}