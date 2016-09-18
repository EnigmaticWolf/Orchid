<?php

namespace Orchid\Entity;

use Closure;
use RuntimeException;

abstract class Validator
{
    protected $data  = [];
    protected $field = null;
    protected $rule  = [];
    protected $error = [];

    /**
     * Validator constructor
     *
     * @param array $data
     */
    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * Select required field validation
     *
     * @param string $field
     *
     * @return $this
     */
    public function attr($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Select not required (optional) field validation
     *
     * @param string $field
     *
     * @return $this
     */
    public function option($field)
    {
        $this->field = null;

        if (!empty($this->data[$field])) {
            $this->field = $field;
        }

        return $this;
    }

    /**
     * Adds to selected field validation rule
     *
     * @param Closure $validator
     * @param string  $message
     *
     * @return $this
     * @throws RuntimeException
     */
    public function addRule($validator, $message = '')
    {
        if ($this->field) {
            $this->rule[$this->field][] = [
                'validator' => $validator,
                'message'   => $message,
            ];
        }

        return $this;
    }

    /**
     * Performs validation on the fields specified rules
     *
     * @return array|bool
     */
    public function validate()
    {
        $this->error = [];

        foreach ($this->rule as $field => $rules) {
            foreach ($rules as $rule) {
                if ($rule['validator']($this->data[$field]) !== true) {
                    $this->error[$field] = $rule['message'] ? $rule['message'] : false;
                    break;
                }
            }
        }

        return $this->error ? $this->error : true;
    }
}
