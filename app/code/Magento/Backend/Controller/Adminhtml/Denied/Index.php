<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Denied;

use Magento\Backend\Controller\Adminhtml\Denied;
use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGet;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPost;
/**
 * Denied Action
 */
class Index extends Denied implements Http_Get, Http_Post
{
    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _is_allowed()
    {
        return true;
    }
}