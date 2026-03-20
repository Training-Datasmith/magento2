<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * Event object and dispatcher
 *
 * @api
 * @since 100.0.2
 */
class Event extends \Magento\Framework\Data_Object
{
    /**
     * Observers collection
     *
     * @var \Magento\Framework\Event\Observer\Collection
     */
    protected $_observers;
    /**
     * Initializes observers collection
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->_observers = new \Magento\Framework\Event\Observer\Collection();
        parent::__construct($data);
    }
    /**
     * Returns all the registered observers for the event
     *
     * @return \Magento\Framework\Event\Observer\Collection
     */
    public function get_observers()
    {
        return $this->_observers;
    }
    /**
     * Register an observer for the event
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function add_observer(\Magento\Framework\Event\Observer $observer)
    {
        $this->get_observers()->add_observer($observer);
        return $this;
    }
    /**
     * Removes an observer by its name
     *
     * @param string $observerName
     *
     * @return $this
     */
    public function remove_observer_by_name($observer_name)
    {
        $this->get_observers()->remove_observer_by_name($observer_name);
        return $this;
    }
    /**
     * Dispatches the event to registered observers
     *
     * @return $this
     */
    public function dispatch()
    {
        $this->get_observers()->dispatch($this);
        return $this;
    }
    /**
     * Retrieve event name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->_data['name'] ?? null;
    }
    /**
     * Set name
     *
     * @param string $data
     *
     * @return $this
     */
    public function set_name($data)
    {
        $this->_data['name'] = $data;
        return $this;
    }
    /**
     * Get block
     *
     * @return mixed
     */
    public function get_block()
    {
        return $this->_get_data('block');
    }
}