<?php

namespace AEngine\Orchid\Entity\Interfaces\Http;

use AEngine\Orchid\Entity\Interfaces\CollectionInterface;

/**
 * Headers Interface
 */
interface HeadersInterface extends CollectionInterface
{
    public function add($key, $value);

    public function normalizeKey($key);
}
