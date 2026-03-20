<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

/**
 * Interface \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface
 *
 * @api
 */
interface HandlerInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function handle(\Magento\Catalog\Model\Product $product);
}
