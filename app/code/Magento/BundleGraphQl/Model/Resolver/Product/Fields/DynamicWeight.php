<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Product\Fields;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * @inheritdoc
 */
class Dynamic_Weight implements Resolver_Interface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        $result = null;
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['weight_type']) ? !$value['weight_type'] : null;
        }
        return $result;
    }
}