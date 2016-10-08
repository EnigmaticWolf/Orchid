<?php

namespace AEngine\Orchid\Entity\Validate;

use Closure;

trait Type
{
    /**
     * Checked value should be empty
     *
     * @return Closure
     */
    public function isEmpty()
    {
        return function ($field) {
            return empty($field);
        };
    }

    /**
     * Checked value should not be empty
     *
     * @return Closure
     */
    public function isNotEmpty()
    {
        return function ($field) {
            return !empty($field);
        };
    }

    /**
     * Checked value should be of type Boolean
     *
     * @return Closure
     */
    public function isBoolean()
    {
        return function ($field) {
            return is_bool($field);
        };
    }

    /**
     * Checked value should be a number
     *
     * @return Closure
     */
    public function isNumeric()
    {
        return function ($field) {
            return is_numeric($field);
        };
    }

    /**
     * Checked value should be a string
     *
     * @return Closure
     */
    public function isString()
    {
        return function ($field) {
            return is_string($field);
        };
    }

    /**
     * Checked value will be converted to a Boolean
     * For values of '1', 'true', 'on' and 'yes' - true
     * For values of '0', 'false', 'off', 'no' and '' - false
     *
     * @return Closure
     */
    public function toBoolean()
    {
        return function (&$field) {
            if (($bool = filter_var($field, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) !== null) {
                $field = $bool;
            }

            return true;
        };
    }

    /**
     * Checked value will be converted to an Integer
     *
     * @return Closure
     */
    public function toInteger()
    {
        return function (&$field) {
            $field = (int)$field;

            return true;
        };
    }

    /**
     * Checked value will be converted to toDouble
     *
     * @param int $precision rounding precision
     *
     * @return Closure
     */
    public function toDouble($precision = 0)
    {
        return function (&$field) use ($precision) {
            $field = round((double)$field, $precision);

            return true;
        };
    }

    /**
     * Checked value will be converted to a String
     *
     * @return Closure
     */
    public function toString()
    {
        return function (&$field) {
            $field = (String)$field;

            return true;
        };
    }
}
