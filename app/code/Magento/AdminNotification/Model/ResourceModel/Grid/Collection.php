<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/**
 * AdminNotification Inbox model
 */
namespace Magento\Admin_Notification\Model\Resource_Model\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _init_select(): static
    {
        parent::_init_select();
        $this->add_remove_filter();
        return $this;
    }
}