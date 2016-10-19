<?php

namespace AEngine\Orchid\Entity\Interfaces\Http;

/**
 * Headers Interface
 */
interface HeadersInterface extends CollectionInterface
{
    public function add($key, $value);

    public function normalizeKey($key);
}
