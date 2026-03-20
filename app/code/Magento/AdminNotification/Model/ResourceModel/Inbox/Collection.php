<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model\Inbox;

/**
 * AdminNotification Inbox model
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Admin_Notification\Model\Inbox::class, \Magento\Admin_Notification\Model\Resource_Model\Inbox::class);
    }
    /**
     * Add remove filter
     *
     * @return $this
     */
    public function add_remove_filter(): static
    {
        $this->get_select()->where('is_remove=?', 0);
        return $this;
    }
}