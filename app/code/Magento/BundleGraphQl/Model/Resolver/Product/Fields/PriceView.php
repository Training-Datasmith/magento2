<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Product\Fields;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Enum_Lookup;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * @inheritdoc
 */
class Price_View implements Resolver_Interface
{
    /**
     * @var EnumLookup
     */
    private $enum_lookup;
    /**
     * @param EnumLookup $enumLookup
     */
    public function __construct(Enum_Lookup $enum_lookup)
    {
        $this->enum_lookup = $enum_lookup;
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        $result = null;
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['price_view']) ? $this->enum_lookup->get_enum_value_from_field('PriceViewEnum', $value['price_view']) : null;
        }
        return $result;
    }
}