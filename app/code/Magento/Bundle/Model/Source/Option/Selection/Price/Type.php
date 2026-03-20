<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Source\Option\Selection\Price;

use Magento\Bundle\Api\Data\Link_Interface;
/**
 * Extended Attributes Source Model
 *
 * @api
 * @since 100.0.2
 */
class Type implements \Magento\Framework\Option\Array_Interface
{
    /**
     * @return array
     */
    public function to_option_array()
    {
        return [['value' => Link_Interface::PRICE_TYPE_FIXED, 'label' => __('Fixed')], ['value' => Link_Interface::PRICE_TYPE_PERCENT, 'label' => __('Percent')]];
    }
}