<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Block\System\Messages;

use Magento\Framework\Notification\MessageInterface;

/**
 * @api
 * @since 100.0.2
 */
class UnreadMessagePopup extends \Magento\Backend\Block\Template
{
    /**
     * List of item classes per severity
     *
     * @var array
     */
    protected $_itemClasses = [
        MessageInterface::SEVERITY_CRITICAL => 'error',
        MessageInterface::SEVERITY_MAJOR => 'warning',
    ];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        /**
         * System Message list
         */
        protected \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized $_messages,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (count($this->_messages->getUnread())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve list of unread messages
     *
     * @return MessageInterface[]
     */
    public function getUnreadMessages()
    {
        return $this->_messages->getUnread();
    }

    /**
     * Retrieve popup title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getPopupTitle()
    {
        $messageCount = count($this->_messages->getUnread());
        if ($messageCount > 1) {
            return __('You have %1 new system messages', $messageCount);
        }
        return __('You have %1 new system message', $messageCount);
    }

    /**
     * Retrieve item class by severity
     *
     * @return string
     */
    public function getItemClass(MessageInterface $message)
    {
        return $this->_itemClasses[$message->getSeverity()];
    }
}
