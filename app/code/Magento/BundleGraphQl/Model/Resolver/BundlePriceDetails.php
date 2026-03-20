<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
use Magento\Quote\Api\Cart_Repository_Interface;
use Magento\Quote\Api\Data\Cart_Item_Interface;
class Bundle_Price_Details implements Resolver_Interface
{
    /**
     * BundlePriceDetails Constructor
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(private readonly Cart_Repository_Interface $cart_repository)
    {
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new Localized_Exception(__('"model" value should be specified'));
        }
        /** @var Product $product */
        $product = $value['model'];
        $price = $product->get_price();
        $final_price = $product->get_final_price();
        $discount_percentage = $price ? 100 - $final_price * 100 / $price : 0;
        if ((int) $product->get_price_type() === Price::PRICE_TYPE_DYNAMIC && isset($value['cart_item'])) {
            $discount_percentage = $this->get_discount_percentage_for_bundle_product($value['cart_item']);
        }
        return ['main_price' => $price, 'main_final_price' => $final_price, 'discount_percentage' => $discount_percentage];
    }
    /**
     * Calculate discount percentage for bundle product with dynamic pricing enabled
     *
     * @param CartItemInterface $cartItem
     * @return float
     * @throws NoSuchEntityException
     */
    private function get_discount_percentage_for_bundle_product(Cart_Item_Interface $cart_item): float
    {
        if (empty($cart_item->get_applied_rule_ids())) {
            return 0;
        }
        $item_amount = 0;
        $discount_amount = 0;
        $cart = $this->cart_repository->get($cart_item->get_quote_id());
        foreach ($cart->get_all_items() as $item) {
            if ($item->get_parent_item_id() == $cart_item->get_id()) {
                $item_amount += $item->get_price();
                $discount_amount += $item->get_discount_amount();
            }
        }
        if ($item_amount && $discount_amount) {
            return $discount_amount / $item_amount * 100;
        }
        return 0;
    }
}