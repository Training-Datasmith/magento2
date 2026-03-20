<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Collection of events
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event;
class Collection
{
    /**
     * Array of events in the collection
     *
     * @var array
     */
    protected $events;
    /**
     * Global observers
     *
     * For example regex observers will watch all events that
     *
     * @var Observer\Collection
     */
    protected $global_observers;
    /**
     * Initializes global observers collection
     *
     * @param array $events
     * @param Observer\Collection $observerCollection
     */
    public function __construct(array $events = [], ?Observer\Collection $observer_collection = null)
    {
        $this->events = $events;
        $this->global_observers = !$observer_collection ? new Observer\Collection() : $observer_collection;
    }
    /**
     * Returns all registered events in collection
     *
     * @return array
     */
    public function get_all_events()
    {
        return $this->events;
    }
    /**
     * Returns all registered global observers for the collection of events
     *
     * @return Observer\Collection
     */
    public function get_global_observers()
    {
        return $this->global_observers;
    }
    /**
     * Returns event by its name
     *
     * If event doesn't exist creates new one and returns it
     *
     * @param string $eventName
     * @return Event
     */
    public function get_event_by_name($event_name)
    {
        if (!isset($this->events[$event_name])) {
            $this->add_event(new Event(['name' => $event_name]));
        }
        return $this->events[$event_name];
    }
    /**
     * Register an event for this collection
     *
     * @param Event $event
     * @return $this
     */
    public function add_event(Event $event)
    {
        $this->events[$event->get_name()] = $event;
        return $this;
    }
    /**
     * Register an observer
     *
     * If observer has event_name property it will be registered for this specific event.
     * If not it will be registered as global observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function add_observer(Observer $observer)
    {
        $event_name = $observer->get_event_name();
        if ($event_name) {
            $this->get_event_by_name($event_name)->add_observer($observer);
        } else {
            $this->get_global_observers()->add_observer($observer);
        }
        return $this;
    }
    /**
     * Dispatch event name with optional data
     *
     * Will dispatch specific event and will try all global observers
     *
     * @param string $eventName
     * @param array $data
     * @return $this
     */
    public function dispatch($event_name, array $data = [])
    {
        $event = $this->get_event_by_name($event_name);
        $event->add_data($data)->dispatch();
        $this->get_global_observers()->dispatch($event);
        return $this;
    }
}