<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Tax;

use Magento\Bundle\Model\Product\Price as BundleProductPrice;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Bundle\Model\Quote\Item\Option as BundleQuoteItemOption;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Tax\Block\Item\Price\Renderer as ItemPriceRenderer;
class Block_Item_Price_Renderer
{
    /**
     * @var BundleQuoteItemOption
     */
    private Bundle_Quote_Item_Option $bundle_quote_item_option;
    /**
     * @var JsonSerializer
     */
    private Json_Serializer $serializer;
    /**
     * @param BundleQuoteItemOption $bundleQuoteItemOption
     * @param JsonSerializer $serializer
     */
    public function __construct(Bundle_Quote_Item_Option $bundle_quote_item_option, Json_Serializer $serializer)
    {
        $this->bundle_quote_item_option = $bundle_quote_item_option;
        $this->serializer = $serializer;
    }
    /**
     * Recalculate price conversion for the bundle product.
     *
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param float $result
     * @return float
     */
    public function after_get_item_display_price_excl_tax(Item_Price_Renderer $item_price_renderer, float $result): float
    {
        if ($item_price_renderer->get_item()->get_product_type() === Bundle_Product_Type::TYPE_CODE) {
            $bundle_product = $item_price_renderer->get_item()->get_product();
            $bundle_selection_options = $this->bundle_quote_item_option->get_selection_options($bundle_product);
            if (empty($bundle_selection_options) || $bundle_product->get_price_type() == Bundle_Product_Price::PRICE_TYPE_FIXED) {
                return $result;
            }
            $price = 0.0;
            foreach ($bundle_selection_options as $bundle_selection_option) {
                $selection_option_value = $this->serializer->unserialize(reset($bundle_selection_option)['value']);
                $price += $selection_option_value['price'] * $selection_option_value['qty'];
            }
            return $price;
        }
        return $result;
    }
}