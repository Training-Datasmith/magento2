<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Plugin;

/**
 * Make price validation optional for bundle dynamic
 */
class Price_Backend
{
    /**
     * Around validate
     *
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $object
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function around_validate(\Magento\Catalog\Model\Product\Attribute\Backend\Price $subject, \Closure $proceed, $object)
    {
        if ($object instanceof \Magento\Catalog\Model\Product && $object->get_type_id() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE && $object->get_price_type() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            return true;
        }
        return $proceed($object);
    }
}