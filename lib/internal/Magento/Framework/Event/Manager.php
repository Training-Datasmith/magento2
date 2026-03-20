<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

/**
 * Event manager used to dispatch global events.
 */
class Manager implements Manager_Interface
{
    /**
     * Events cache
     *
     * @var array
     */
    protected $_events = [];
    /**
     * Event invoker
     *
     * @var InvokerInterface
     */
    protected $_invoker;
    /**
     * @var ConfigInterface
     */
    protected $_event_config;
    /**
     * @param InvokerInterface $invoker
     * @param ConfigInterface $eventConfig
     */
    public function __construct(Invoker_Interface $invoker, Config_Interface $event_config)
    {
        $this->_invoker = $invoker;
        $this->_event_config = $event_config;
    }
    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiple observers matching event name pattern
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function dispatch($event_name, array $data = [])
    {
        $event_name = $event_name !== null ? mb_strtolower($event_name) : '';
        \Magento\Framework\Profiler::start('EVENT:' . $event_name, ['group' => 'EVENT', 'name' => $event_name]);
        foreach ($this->_event_config->get_observers($event_name) as $observer_config) {
            $event = new \Magento\Framework\Event($data);
            $event->set_name($event_name);
            $wrapper = new Observer();
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $wrapper->set_data(array_merge(['event' => $event], $data));
            \Magento\Framework\Profiler::start('OBSERVER:' . $observer_config['name']);
            $this->_invoker->dispatch($observer_config, $wrapper);
            \Magento\Framework\Profiler::stop('OBSERVER:' . $observer_config['name']);
        }
        \Magento\Framework\Profiler::stop('EVENT:' . $event_name);
    }
}