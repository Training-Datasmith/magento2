<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block\System;

use Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection\Synchronized;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Notification\Message_Interface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
/**
 * AdminNotification Messages class
 */
class Messages extends Template
{
    public function __construct(
        Template_Context $context,
        /**
         * Synchronized Message collection
         */
        protected \Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection\Synchronized $_messages,
        /**
         * @deprecated 100.3.0
         * @see \Magento\Framework\Serialize\Serializer\Json
         */
        protected \Magento\Framework\Json\Helper\Data $json_helper,
        private readonly Json_Serializer $serializer,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _to_html()
    {
        if (count($this->_messages->get_items())) {
            return parent::_to_html();
        }
        return '';
    }
    /**
     * Retrieve message list
     *
     * @return MessageInterface[]|null
     */
    public function get_last_critical(): ?\Magento\Framework\Data_Object
    {
        $items = array_values($this->_messages->get_items());
        if (!empty($items) && current($items)->get_severity() === Message_Interface::SEVERITY_CRITICAL) {
            return current($items);
        }
        return null;
    }
    /**
     * Retrieve number of critical messages
     *
     * @return int
     */
    public function get_critical_count()
    {
        return $this->_messages->get_count_by_severity(Message_Interface::SEVERITY_CRITICAL);
    }
    /**
     * Retrieve number of major messages
     *
     * @return int
     */
    public function get_major_count()
    {
        return $this->_messages->get_count_by_severity(Message_Interface::SEVERITY_MAJOR);
    }
    /**
     * Check whether system messages are present
     */
    public function has_messages(): bool
    {
        return (bool) count($this->_messages->get_items());
    }
    /**
     * Retrieve message list url
     *
     * @return string
     */
    protected function _get_messages_url()
    {
        return $this->get_url('adminhtml/system_message/list');
    }
    /**
     * Initialize system message dialog widget
     *
     * @return string
     */
    public function get_system_message_dialog_json()
    {
        return $this->serializer->serialize(['systemMessageDialog' => ['buttons' => [], 'modalClass' => 'ui-dialog-active ui-popup-message modal-system-messages', 'ajaxUrl' => $this->_get_messages_url()]]);
    }
}