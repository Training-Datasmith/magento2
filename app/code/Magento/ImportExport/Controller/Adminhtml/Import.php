<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Import controller
 */
abstract class Import extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_ImportExport::import';
}
