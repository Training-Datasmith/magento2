<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Critical extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
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
     * Initialization of the select object
     *
     * @return $this
     */
    protected function _init_select(): static
    {
        parent::_init_select();
        $this->add_order('notification_id', self::SORT_ORDER_DESC)->add_field_to_filter('is_read', ['neq' => 1])->add_field_to_filter('is_remove', ['neq' => 1])->add_field_to_filter('severity', \Magento\Framework\Notification\Message_Interface::SEVERITY_CRITICAL)->set_page_size(1);
        return $this;
    }
}