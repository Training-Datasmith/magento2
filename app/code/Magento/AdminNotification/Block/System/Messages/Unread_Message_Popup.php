<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block\System\Messages;

use Magento\Framework\Notification\Message_Interface;
/**
 * @api
 * @since 100.0.2
 */
class Unread_Message_Popup extends \Magento\Backend\Block\Template
{
    /**
     * List of item classes per severity
     *
     * @var array
     */
    protected $_item_classes = [Message_Interface::SEVERITY_CRITICAL => 'error', Message_Interface::SEVERITY_MAJOR => 'warning'];
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        /**
         * System Message list
         */
        protected \Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection\Synchronized $_messages,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
    /**
     * Render block
     *
     * @return string
     */
    protected function _to_html()
    {
        if (count($this->_messages->get_unread())) {
            return parent::_to_html();
        }
        return '';
    }
    /**
     * Retrieve list of unread messages
     *
     * @return MessageInterface[]
     */
    public function get_unread_messages()
    {
        return $this->_messages->get_unread();
    }
    /**
     * Retrieve popup title
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_popup_title()
    {
        $message_count = count($this->_messages->get_unread());
        if ($message_count > 1) {
            return __('You have %1 new system messages', $message_count);
        }
        return __('You have %1 new system message', $message_count);
    }
    /**
     * Retrieve item class by severity
     *
     * @return string
     */
    public function get_item_class(Message_Interface $message)
    {
        return $this->_item_classes[$message->get_severity()];
    }
}