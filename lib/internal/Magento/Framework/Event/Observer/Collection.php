<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Event observer collection
 */
namespace Magento\Framework\Event\Observer;

/**
 * @api
 * @since 100.0.2
 */
class Collection
{
    /**
     * Array of observers
     *
     * @var array
     */
    protected $_observers;
    /**
     * Initializes observers
     */
    public function __construct()
    {
        $this->_observers = [];
    }
    /**
     * Returns all observers in the collection
     *
     * @return array
     */
    public function get_all_observers()
    {
        return $this->_observers;
    }
    /**
     * Returns observer by its name
     *
     * @param string $observerName
     * @return \Magento\Framework\Event\Observer
     */
    public function get_observer_by_name($observer_name)
    {
        return $this->_observers[$observer_name];
    }
    /**
     * Adds an observer to the collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function add_observer(\Magento\Framework\Event\Observer $observer)
    {
        $observer_name = $observer->get_name() ?? '';
        $this->_observers[$observer_name] = $observer;
        return $this;
    }
    /**
     * Removes an observer from the collection by its name
     *
     * @param string $observerName
     * @return $this
     */
    public function remove_observer_by_name($observer_name)
    {
        unset($this->_observers[$observer_name]);
        return $this;
    }
    /**
     * Dispatches an event to all observers in the collection
     *
     * @param \Magento\Framework\Event $event
     * @return $this
     */
    public function dispatch(\Magento\Framework\Event $event)
    {
        foreach ($this->_observers as $observer) {
            $observer->dispatch($event);
        }
        return $this;
    }
}