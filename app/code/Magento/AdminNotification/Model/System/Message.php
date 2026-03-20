<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System;

/**
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Framework\Model\Abstract_Model implements \Magento\Framework\Notification\Message_Interface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Admin_Notification\Model\Resource_Model\System\Message::class);
    }
    /**
     * Check whether
     */
    public function is_displayed(): bool
    {
        return true;
    }
    /**
     * Retrieve message text
     *
     * @return string
     */
    public function get_text()
    {
        return $this->get_data('text');
    }
    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function get_severity()
    {
        return $this->_get_data('severity');
    }
    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function get_identity()
    {
        return $this->_get_data('identity');
    }
}