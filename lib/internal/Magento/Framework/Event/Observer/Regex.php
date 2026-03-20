<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Event regex observer object
 */
namespace Magento\Framework\Event\Observer;

class Regex extends \Magento\Framework\Event\Observer
{
    /**
     * Checkes the observer's event_regex against event's name
     *
     * @param \Magento\Framework\Event $event
     * @return boolean
     */
    public function is_valid_for(\Magento\Framework\Event $event)
    {
        return $event->get_name() !== null ? preg_match($this->get_event_regex(), $event->get_name()) : false;
    }
}