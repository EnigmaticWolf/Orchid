<?php

namespace AEngine\Orchid\Interfaces\Http;

use AEngine\Orchid\Interfaces\CollectionInterface;

/**
 * Headers Interface
 */
interface HeadersInterface extends CollectionInterface
{
    public function add($key, $value);

    public function normalizeKey($key);
}
