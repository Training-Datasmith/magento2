<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/**
 * Collection of unread notifications
 */
namespace Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Unread extends \Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection
{
    /**
     * Init collection select
     */
    protected function _init_select(): static
    {
        parent::_init_select();
        $this->add_filter('is_remove', 0);
        $this->add_filter('is_read', 0);
        $this->set_order('date_added');
        return $this;
    }
}