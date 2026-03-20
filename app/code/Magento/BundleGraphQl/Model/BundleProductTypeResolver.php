<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model;

use Magento\Bundle\Model\Product\Type as Type;
use Magento\Framework\Graph_Ql\Query\Resolver\Type_Resolver_Interface;
/**
 * @inheritdoc
 */
class Bundle_Product_Type_Resolver implements Type_Resolver_Interface
{
    public const BUNDLE_PRODUCT = 'BundleProduct';
    /**
     * @inheritdoc
     */
    public function resolve_type(array $data): string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_CODE) {
            return self::BUNDLE_PRODUCT;
        }
        return '';
    }
}