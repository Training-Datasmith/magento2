<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Block;

/**
 * Admin notification window block
 *
 * @api
 * @since 100.0.2
 */
class Window extends \Magento\Backend\Block\Template
{
    /**
     * XML path of Severity icons url
     */
    public const XML_SEVERITY_ICONS_URL_PATH = 'system/adminnotification/severity_icons_url';

    /**
     * @var string
     */
    protected $_severityIconsUrl;

    /**
     * @var \Magento\AdminNotification\Model\Inbox
     */
    protected $_latestItem;

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_isScopePrivate;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        /**
         * Authentication
         */
        protected \Magento\Backend\Model\Auth\Session $_authSession,
        /**
         * Critical messages collection
         */
        protected \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical $_criticalCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShow()) {
            $this->setHeaderText($this->escapeHtml(__('Incoming Message')));
            $this->setCloseText($this->escapeHtml(__('close')));
            $this->setReadDetailsText($this->escapeHtml(__('Read Details')));
            $this->setNoticeMessageText($this->escapeHtml($this->_getLatestItem()->getTitle()));
            $this->setNoticeMessageUrl($this->escapeUrl($this->_getLatestItem()->getUrl()));
            $this->setSeverityText('critical');
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve latest critical item
     *
     * @return bool|\Magento\AdminNotification\Model\Inbox
     */
    protected function _getLatestItem()
    {
        if ($this->_latestItem == null) {
            $items = array_values($this->_criticalCollection->getItems());
            $this->_latestItem = false;
            if (count($items)) {
                $this->_latestItem = $items[0];
            }
        }
        return $this->_latestItem;
    }

    /**
     * Check whether block should be displayed
     */
    public function canShow(): bool
    {
        return $this->_authSession->isFirstPageAfterLogin() && $this->_getLatestItem();
    }
}
