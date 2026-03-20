<?php

declare (strict_types=1);
/**
 * Event configuration model
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event\Config\Data;
class Config implements Config_Interface
{
    /**
     * Modules configuration model
     *
     * @var Data
     */
    protected $_data_container;
    /**
     * @param Data $dataContainer
     */
    public function __construct(Data $data_container)
    {
        $this->_data_container = $data_container;
    }
    /**
     * Get observers by event name
     *
     * @param string $eventName
     * @return null|array|mixed
     */
    public function get_observers($event_name)
    {
        return $this->_data_container->get($event_name, []);
    }
}