<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event;
/**
 * @api
 * @since 100.0.2
 */
class Observer extends \Magento\Framework\Data_Object
{
    /**
     * Checks the observer's event_regex against event's name
     *
     * @param Event $event
     * @return boolean
     */
    public function is_valid_for(Event $event)
    {
        return $this->get_event_name() === $event->get_name();
    }
    /**
     * Dispatches an event to observer's callback
     *
     * @param Event $event
     * @return $this
     */
    public function dispatch(Event $event)
    {
        if (!$this->is_valid_for($event)) {
            return $this;
        }
        $callback = $this->get_callback();
        $this->set_event($event);
        $_profiler_key = 'OBSERVER: ';
        if (is_object($callback[0])) {
            $_profiler_key .= get_class($callback[0]);
        } else {
            $_profiler_key .= (string) $callback[0];
        }
        $_profiler_key .= ' -> ' . $callback[1];
        \Magento\Framework\Profiler::start($_profiler_key);
        call_user_func($callback, $this);
        \Magento\Framework\Profiler::stop($_profiler_key);
        return $this;
    }
    /**
     * @return string
     */
    public function get_name()
    {
        return $this->get_data('name');
    }
    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function set_name($data)
    {
        return $this->set_data('name', $data);
    }
    /**
     * @return string
     */
    public function get_event_name()
    {
        return $this->get_data('event_name');
    }
    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function set_event_name($data)
    {
        return $this->set_data('event_name', $data);
    }
    /**
     * @return string
     */
    public function get_callback()
    {
        return $this->get_data('callback');
    }
    /**
     * @param string $data
     * @return \Magento\Framework\DataObject
     */
    public function set_callback($data)
    {
        return $this->set_data('callback', $data);
    }
    /**
     * Get observer event object
     *
     * @return Event
     */
    public function get_event()
    {
        return $this->get_data('event');
    }
    /**
     * @param mixed $data
     * @return \Magento\Framework\DataObject
     */
    public function set_event($data)
    {
        return $this->set_data('event', $data);
    }
}