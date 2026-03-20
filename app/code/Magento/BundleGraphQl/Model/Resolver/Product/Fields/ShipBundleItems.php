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
class Ship_Bundle_Items implements Resolver_Interface
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
        $result = isset($value['shipment_type']) && $value['type_id'] === Bundle::TYPE_CODE ? $this->enum_lookup->get_enum_value_from_field('ShipBundleItemsEnum', $value['shipment_type']) : null;
        return $result;
    }
}