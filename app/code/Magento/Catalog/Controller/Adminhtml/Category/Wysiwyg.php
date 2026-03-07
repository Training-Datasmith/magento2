<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Controller\Adminhtml\Category;

class Wysiwyg extends \Magento\Catalog\Controller\Adminhtml\Product\Wysiwyg
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Catalog::categories';
}
