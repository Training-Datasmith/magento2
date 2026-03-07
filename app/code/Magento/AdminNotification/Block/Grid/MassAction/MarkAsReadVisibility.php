<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\MassAction;

use Magento\AdminNotification\Controller\Adminhtml\Notification\MarkAsRead;
use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class checks if mark as read action can be displayed on massaction list
 */
class MarkAsReadVisibility implements VisibilityCheckerInterface
{
    public function __construct(private readonly AuthorizationInterface $authorization)
    {
    }

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        return $this->authorization->isAllowed(MarkAsRead::ADMIN_RESOURCE);
    }
}
