<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\Abstract_Action;
/**
 * Index backend controller
 */
abstract class Index extends Abstract_Action
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