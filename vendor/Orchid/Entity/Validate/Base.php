<?php

namespace Orchid\Entity\Validate;

use Closure;

trait Base
{
    public static $DATE_RU = 'd.m.Y'; // Russian date format (ГОСТ Р 6.30-2003 (п. 3.11))
    public static $DATE_EN = 'd-m-Y'; // English date format
    public static $DATE_US = 'm-d-Y'; // US date format
    public static $DATE_DB = 'Y-m-d'; // data bases date format (ISO 8601)

    /**
     * Checked value will be converted to a date in the specified format
     *
     * @param string $format
     *
     * @return Closure
     */
    public function date($format = '')
    {
        return function (&$field) use ($format) {
            if (($time = strtotime($field)) !== false) {
                $field = date($format, $time);

                return true;
            }

            return false;
        };
    }

    /**
     * Checked value should be greater than or equal to the specified
     * In case if the value - the string length of the string is checked
     *
     * @param int $min
     *
     * @return Closure
     */
    public function min($min = -INF)
    {
        return function ($field) use ($min) {
            if (is_string($field)) {
                return mb_strlen($field) >= $min;
            }
            if (is_numeric($field)) {
                return $field >= $min;
            }

            return false;
        };
    }

    /**
     * Checked must be less than or equal to the specified
     * In case if the value - the string length of the string is checked
     *
     * @param int $max
     *
     * @return Closure
     */
    public function max($max = INF)
    {
        return function ($field) use ($max) {
            if (is_string($field)) {
                return mb_strlen($field) <= $max;
            }
            if (is_numeric($field)) {
                return $field <= $max;
            }

            return false;
        };
    }
}
