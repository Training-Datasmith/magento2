<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Event\Manager_Interface;
class Event_Manager
{
    /**
     * @var ManagerInterface
     */
    private $event_manager;
    /**
     * EventManager constructor.
     * @param ManagerInterface $eventManager
     */
    public function __construct(Manager_Interface $event_manager)
    {
        $this->event_manager = $event_manager;
    }
    /**
     * Get entity prefix for event
     *
     * @param string $entityType
     * @return string
     */
    private function resolve_entity_prefix($entity_type)
    {
        return $entity_type !== null ? strtolower(str_replace('\\', '_', $entity_type)) : '';
    }
    /**
     * Method to dispatch entity event.
     *
     * @param string $entityType
     * @param string $eventSuffix
     * @param array $data
     * @return void
     */
    public function dispatch_entity_event($entity_type, $event_suffix, array $data = [])
    {
        $this->event_manager->dispatch($this->resolve_entity_prefix($entity_type) . '_' . $event_suffix, $data);
    }
    /**
     * Method to dispatch.
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function dispatch($event_name, array $data = [])
    {
        $this->event_manager->dispatch($event_name, $data);
    }
}