<?php

namespace Orchid\Entity;

use Orchid\Entity\Exception\FileNotFoundException;

class View
{
    /**
     * Global layout template path
     *
     * @var string
     */
    public static $layout;
    /**
     * Array of global data for global & current templates
     *
     * @var array
     */
    protected static $globalData = [];
    /**
     * Current template path
     *
     * @var string
     */
    protected $file;
    /**
     * Array of data for current template
     *
     * @var array
     */
    protected $data;

    /**
     * View constructor
     *
     * @param string $file
     * @param array  $data
     */
    public function __construct($file, array $data = [])
    {
        $this->file = $file;
        $this->data = $data;
    }

    /**
     * Set global data passed to the view as properties
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function setGlobal($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                View::$globalData[$k] = $v;
            }
        } else {
            View::$globalData[$key] = $value;
        }
    }

    /**
     * Set data passed to the view as properties
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value = null)
    {
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
     * @return string
     * @throws FileNotFoundException
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render the template, all dynamically set properties
     * will be available inside the view file as variables
     *
     * <code>
     * View::$layout = 'path/to/file'; // global template
     * $view = new View('path/to/file');
     * $view->set('title', 'Page title');
     * echo $view->render();
     * </code>
     *
     * @see View::fetch
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function render()
    {
        if (!empty($this->file)) {
            $this->data['content'] = View::fetch($this->file, $this->data);
        }

        return View::fetch(static::$layout, $this->data);
    }

    /**
     * Render the template
     *
     * <code>
     * View::fetch(
     *  $this->path('path/to/file'), [
     *   'hello' => 'Hello World!',
     * ]);
     * </code>
     *
     * @param string $_file
     * @param array  $_data
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public static function fetch($_file, array $_data = [])
    {
        if ($_data) {
            extract($_data, EXTR_SKIP);
        }

        if (View::$globalData) {
            extract(View::$globalData, EXTR_SKIP);
        }

        if ($_file && file_exists($_file)) {
            ob_start();
            require $_file;

            return ob_get_clean();
        }

        throw new FileNotFoundException('Could not find the template file');
    }
}
