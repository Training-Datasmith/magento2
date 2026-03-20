<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Notification\Block\Grid\Mass_Action;

use Magento\Admin_Notification\Controller\Adminhtml\Notification\Mark_As_Read;
use Magento\Backend\Block\Widget\Grid\Massaction\Visibility_Checker_Interface;
use Magento\Framework\Authorization_Interface;
/**
 * Class checks if mark as read action can be displayed on massaction list
 */
class Mark_As_Read_Visibility implements Visibility_Checker_Interface
{
    public function __construct(private readonly Authorization_Interface $authorization)
    {
    }
    /**
     * @inheritdoc
     */
    public function is_visible()
    {
        return $this->authorization->is_allowed(Mark_As_Read::ADMIN_RESOURCE);
    }
}