<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block;

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
    protected $_severity_icons_url;
    /**
     * @var \Magento\AdminNotification\Model\Inbox
     */
    protected $_latest_item;
    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_is_scope_private;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        /**
         * Authentication
         */
        protected \Magento\Backend\Model\Auth\Session $_auth_session,
        /**
         * Critical messages collection
         */
        protected \Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection\Critical $_critical_collection,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_is_scope_private = true;
    }
    /**
     * Render block
     *
     * @return string
     */
    protected function _to_html()
    {
        if ($this->can_show()) {
            $this->set_header_text($this->escape_html(__('Incoming Message')));
            $this->set_close_text($this->escape_html(__('close')));
            $this->set_read_details_text($this->escape_html(__('Read Details')));
            $this->set_notice_message_text($this->escape_html($this->_get_latest_item()->get_title()));
            $this->set_notice_message_url($this->escape_url($this->_get_latest_item()->get_url()));
            $this->set_severity_text('critical');
            return parent::_to_html();
        }
        return '';
    }
    /**
     * Retrieve latest critical item
     *
     * @return bool|\Magento\AdminNotification\Model\Inbox
     */
    protected function _get_latest_item()
    {
        if ($this->_latest_item == null) {
            $items = array_values($this->_critical_collection->get_items());
            $this->_latest_item = false;
            if (count($items)) {
                $this->_latest_item = $items[0];
            }
        }
        return $this->_latest_item;
    }
    /**
     * Check whether block should be displayed
     */
    public function can_show(): bool
    {
        return $this->_auth_session->is_first_page_after_login() && $this->_get_latest_item();
    }
}