<?php

namespace Orchid;

use Closure;

class Event
{
    /**
     * Array of event callable
     *
     * @var array
     */
    protected $events = [];

    /**
     * Bind callable listen event
     *
     * @param string        $name
     * @param Closure|array $callable
     * @param int           $priority
     *
     * @return $this
     */
    public function on($name, $callable, $priority = 0)
    {
        $this->events[$name][] = ['callable' => $callable, 'priority' => $priority];

        return $this;
    }

    /**
     * Remove previously-bound callable
     *
     * @param string $name
     *
     * @return $this
     */
    public function off($name)
    {
        unset($this->events[$name]);

        return $this;
    }

    /**
     * Trigger callable's
     *
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function trigger($name, $params = [])
    {
        if (!empty($this->events[$name])) {
            usort($this->events[$name], [$this, 'compare']);

            foreach ($this->events[$name] as $ev) {
                if (is_callable($ev['callable'])) {
                    call_user_func_array($ev['callable'], $params);
                }
            }
        }

        return $this;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return mixed
     */
    protected function compare($a, $b)
    {
        return $b['priority'] - $a['priority'];
    }
}
