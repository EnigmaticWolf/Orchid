<?php

namespace Orchid\Entity\Validate;

use Closure;

trait Str
{
    /**
     * Escapes a string
     *
     * @return Closure
     */
    public function escape()
    {
        return function (&$field) {
            $field = str_replace(
                ['\'', '"', '>', '<', '`', '\\'],
                ['&#039;', '&#34;', '&#62;', '&#60;', '&#96;', '&#92;'],
                $field
            );

            return true;
        };
    }

    /**
     * Checked value is the E-Mail address
     *
     * @return Closure
     */
    public function isEmail()
    {
        return function ($field) {
            return !!filter_var($field, FILTER_VALIDATE_EMAIL);
        };
    }

    /**
     * Checked value is the IP address
     *
     * @return Closure
     */
    public function isIp()
    {
        return function ($field) {
            return !!filter_var($field, FILTER_VALIDATE_IP);
        };
    }
}
