<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model\System;

/**
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * Flag that notifies whether Primary key of table is auto-incremented
     *
     * @var bool
     */
    protected $_is_pk_auto_increment = false;
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('admin_system_messages', 'identity');
    }
}