<?php

declare (strict_types=1);
/**
 * Observer interface
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\ObserverInterface
 *
 * @api
 * @since 100.0.2
 */
interface Observer_Interface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer);
}