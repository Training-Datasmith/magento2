<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Block\System;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * AdminNotification Messages class
 */
class Messages extends Template
{
    public function __construct(
        TemplateContext $context,
        /**
         * Synchronized Message collection
         */
        protected \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized $_messages,
        /**
         * @deprecated 100.3.0
         * @see \Magento\Framework\Serialize\Serializer\Json
         */
        protected \Magento\Framework\Json\Helper\Data $jsonHelper,
        private readonly JsonSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (count($this->_messages->getItems())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve message list
     *
     * @return MessageInterface[]|null
     */
    public function getLastCritical(): ?\Magento\Framework\DataObject
    {
        $items = array_values($this->_messages->getItems());

        if (!empty($items) && current($items)->getSeverity() === MessageInterface::SEVERITY_CRITICAL) {
            return current($items);
        }
        return null;
    }

    /**
     * Retrieve number of critical messages
     *
     * @return int
     */
    public function getCriticalCount()
    {
        return $this->_messages->getCountBySeverity(MessageInterface::SEVERITY_CRITICAL);
    }

    /**
     * Retrieve number of major messages
     *
     * @return int
     */
    public function getMajorCount()
    {
        return $this->_messages->getCountBySeverity(MessageInterface::SEVERITY_MAJOR);
    }

    /**
     * Check whether system messages are present
     */
    public function hasMessages(): bool
    {
        return (bool)count($this->_messages->getItems());
    }

    /**
     * Retrieve message list url
     *
     * @return string
     */
    protected function _getMessagesUrl()
    {
        return $this->getUrl('adminhtml/system_message/list');
    }

    /**
     * Initialize system message dialog widget
     *
     * @return string
     */
    public function getSystemMessageDialogJson()
    {
        return $this->serializer->serialize(
            [
                'systemMessageDialog' => [
                    'buttons' => [],
                    'modalClass' => 'ui-dialog-active ui-popup-message modal-system-messages',
                    'ajaxUrl' => $this->_getMessagesUrl(),
                ],
            ]
        );
    }
}
