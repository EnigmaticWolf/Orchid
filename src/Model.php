<?php

namespace AEngine\Orchid;

use AEngine\Orchid\Interfaces\ModelInterface;

abstract class Model implements ModelInterface
{
    /**
     * Array of fields describing the model
     *
     * @var array
     */
    protected static $field = [];

    /**
     * Model data array
     *
     * @var array
     */
    protected $data = [];

    final public function __construct(array $data = [])
    {
        $this->replace(array_merge(static::$field, $data));
    }

    /**
     * Return value for a key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key] ?? static::$field[$key];
    }

    /**
     * Set value for a key
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (array_key_exists($key, static::$field)) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Set values for all keys
     *
     * @param array $data
     *
     * @return $this
     */
    public function replace(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Check has key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->data[$key] ?? false;
    }

    /**
     * Check whether the model is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return static::$field === $this->data;
    }

    /**
     * Restore default value for key
     *
     * @param string $key
     *
     * @return $this
     */
    public function delete($key)
    {
        $this->data[$key] = static::$field[$key];

        return $this;
    }

    /**
     * Restore default model data
     *
     * @return $this
     */
    public function clear()
    {
        $this->data = static::$field;

        return $this;
    }

    /**
     * Return model as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Return model as string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
