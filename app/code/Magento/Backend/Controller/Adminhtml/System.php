<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\Abstract_Action;
/**
 * System admin controller
 */
abstract class System extends Abstract_Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::system';
}