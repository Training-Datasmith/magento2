<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Notification\Block\Grid\Renderer;

use Magento\Admin_Notification\Controller\Adminhtml\Notification\Mark_As_Read;
use Magento\Admin_Notification\Controller\Adminhtml\Notification\Remove;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer;
use Magento\Framework\App\Action_Interface;
use Magento\Framework\Data_Object;
/**
 * Renderer class for action in the admin notifications grid
 */
class Actions extends Abstract_Renderer
{
    public function __construct(Context $context, protected \Magento\Framework\Url\Helper\Data $_url_helper, array $data = [])
    {
        parent::__construct($context, $data);
    }
    /**
     * Renders grid column
     */
    public function render(Data_Object $row): string
    {
        $read_details_html = $row->get_url() ? '<a class="action-details" target="_blank" href="' . $this->escape_url($row->get_url()) . '">' . __('Read Details') . '</a>' : '';
        $mark_as_read_html = !$row->get_is_read() && $this->_authorization->is_allowed(Mark_As_Read::ADMIN_RESOURCE) ? '<a class="action-mark" href="' . $this->escape_url($this->get_url('*/*/markAsRead/', ['_current' => true, 'id' => $row->get_notification_id()])) . '">' . __('Mark as Read') . '</a>' : '';
        $remove_url = $this->get_url('*/*/remove/', ['_current' => true, 'id' => $row->get_notification_id(), Action_Interface::PARAM_NAME_URL_ENCODED => $this->_url_helper->get_encoded_url()]);
        $remove_html = $this->_authorization->is_allowed(Remove::ADMIN_RESOURCE) ? '<a class="action-delete" href="' . $this->escape_url($remove_url) . '" onClick="deleteConfirm(' . __('\'Are you sure?\'') . ', this.href); return false;">' . __('Remove') . '</a>' : '';
        return sprintf('%s%s%s', $read_details_html, $mark_as_read_html, $remove_html);
    }
}