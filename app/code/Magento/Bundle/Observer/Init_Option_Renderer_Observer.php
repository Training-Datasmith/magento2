<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Observer;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
/**
 * Initiates render options
 */
class Init_Option_Renderer_Observer implements Observer_Interface
{
    /**
     * Initialize product options renderer with bundle specific params
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $block = $observer->get_block();
        $block->add_options_render_cfg('bundle', Configuration::class);
        return $this;
    }
}