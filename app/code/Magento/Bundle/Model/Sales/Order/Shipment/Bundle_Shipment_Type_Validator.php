<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Sales\Order\Shipment;

use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Validator_Interface;
/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class Bundle_Shipment_Type_Validator implements Validator_Interface
{
    /**
     * @inheritdoc
     */
    public function validate($item)
    {
        $result = [];
        if (!$item->is_dummy(true)) {
            return $result;
        }
        $message = 'Cannot create shipment as bundle product "%1" has shipment type "%2". ' . '%3 should be shipped instead.';
        if ($item->get_has_children() && $item->get_product_type() === Type::TYPE_BUNDLE) {
            $result[] = __($message, $item->get_sku(), __('Separately'), __('Bundle product options'));
        }
        if ($item->get_parent_item() && $item->get_parent_item()->get_product_type() === Type::TYPE_BUNDLE) {
            $result[] = __($message, $item->get_parent_item()->get_sku(), __('Together'), __('Bundle product itself'));
        }
        return $result;
    }
}