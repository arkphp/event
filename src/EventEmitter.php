<?php
namespace Ark\Event;

class EventEmitter
{
    const PRIORITY_HIGHEST = 0;
    const PRIORITY_HIGH = 5;
    const PRIORITY_DEFAULT = 10;
    const PRIORITY_LOW = 100;
    const PRIORITY_LOWEST = 10000;

    protected $listeners = [];

    public function on($event, callable $listener, $priority = self::PRIORITY_DEFAULT)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        if (!isset($this->listeners[$event][$priority])) {
            $this->listeners[$event][$priority] = [];
        }

        $this->listeners[$event][$priority][] = $listener;
    }

    public function once($event, callable $listener, $priority = self::PRIORITY_DEFAULT)
    {
        $onceListener = function () use (&$onceListener, $event, $listener) {
            $this->off($event, $onceListener);

            return call_user_func_array($listener, func_get_args());
        };

        $this->on($event, $onceListener, $priority);
    }

    public function off($event, callable $listener = null)
    {
        if ($listener === null) {
            unset($this->listeners[$event]);
        } elseif (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $priority => $listeners) {
                $index = array_search($listener, $listeners, true);

                if (false !== $index) {
                    unset($this->listeners[$event][$priority][$index]);
                }
            }
        }
    }

    public function listeners($event)
    {
        $allListeners = [];
        if (isset($this->listeners[$event])) {
            ksort($this->listeners[$event]);
            foreach ($this->listeners[$event] as $listeners) {
                $allListeners = array_merge($allListeners, $listeners);
            }
        }

        return $allListeners;
    }

    public function emit($event, array $arguments = [])
    {
        foreach ($this->listeners($event) as $listener) {
            if (call_user_func_array($listener, $arguments) === false) {
                break;
            }
        }
    }
}
