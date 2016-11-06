<?php

namespace AEngine\Orchid\Interfaces;

/**
 * Collection Interface
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    public function get($key, $default = null);

    public function set($key, $value);

    public function replace(array $items);

    public function all();

    public function has($key);

    public function remove($key);

    public function clear();

    public function __toString();
}
