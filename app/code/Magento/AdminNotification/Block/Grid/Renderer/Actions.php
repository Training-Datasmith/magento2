<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Controller\Adminhtml\Notification\MarkAsRead;
use Magento\AdminNotification\Controller\Adminhtml\Notification\Remove;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject;

/**
 * Renderer class for action in the admin notifications grid
 */
class Actions extends AbstractRenderer
{
    public function __construct(Context $context, protected \Magento\Framework\Url\Helper\Data $_urlHelper, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     */
    public function render(DataObject $row): string
    {
        $readDetailsHtml = $row->getUrl() ?
            '<a class="action-details" target="_blank" href="' .
            $this->escapeUrl($row->getUrl())
            . '">' .
            __('Read Details') . '</a>' : '';

        $markAsReadHtml = !$row->getIsRead()
            && $this->_authorization->isAllowed(MarkAsRead::ADMIN_RESOURCE) ?
            '<a class="action-mark" href="' . $this->escapeUrl($this->getUrl(
                '*/*/markAsRead/',
                ['_current' => true, 'id' => $row->getNotificationId()]
            )) . '">' . __(
                'Mark as Read'
            ) . '</a>' : '';

        $removeUrl = $this->getUrl(
            '*/*/remove/',
            [
                '_current' => true,
                'id' => $row->getNotificationId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->_urlHelper->getEncodedUrl(),
            ]
        );

        $removeHtml = $this->_authorization->isAllowed(Remove::ADMIN_RESOURCE) ?
            '<a class="action-delete" href="'
            . $this->escapeUrl($removeUrl)
            .'" onClick="deleteConfirm('. __('\'Are you sure?\'') .', this.href); return false;">'
            . __('Remove') .  '</a>' : '';

        return sprintf(
            '%s%s%s',
            $readDetailsHtml,
            $markAsReadHtml,
            $removeHtml,
        );
    }
}
