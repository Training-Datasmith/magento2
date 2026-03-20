<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Block\Data_Providers;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\Tier_Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Block\Argument_Interface;
use Magento\Framework\View\Layout_Interface;
/**
 * Provides additional data for bundle options
 */
class Option_Price_Renderer implements Argument_Interface
{
    /**
     * Parent layout of the block
     *
     * @var LayoutInterface
     */
    private $layout;
    /**
     * @param LayoutInterface $layout
     */
    public function __construct(Layout_Interface $layout)
    {
        $this->layout = $layout;
    }
    /**
     * Format tier price string
     *
     * @param Product $selection
     * @param array $arguments
     * @return string
     */
    public function render_tier_price(Product $selection, array $arguments = []): string
    {
        if (!array_key_exists('zone', $arguments)) {
            $arguments['zone'] = Render::ZONE_ITEM_OPTION;
        }
        $price_html = '';
        /** @var Render $priceRender */
        $price_render = $this->layout->get_block('product.price.render.default');
        if ($price_render !== false) {
            $price_html = $price_render->render(Tier_Price::PRICE_CODE, $selection, $arguments);
        }
        return $price_html;
    }
}