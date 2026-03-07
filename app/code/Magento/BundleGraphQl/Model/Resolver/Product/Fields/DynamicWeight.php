<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Product\Fields;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class DynamicWeight implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $result = null;
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['weight_type']) ? !$value['weight_type'] : null;
        }

        return $result;
    }
}
