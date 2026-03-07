<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class ConfigurableProductTypeResolver implements TypeResolverInterface
{
    /**
     * Configurable product type resolver code
     */
    public const TYPE_RESOLVER = 'ConfigurableProduct';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_CODE) {
            return self::TYPE_RESOLVER;
        }
        return '';
    }
}
