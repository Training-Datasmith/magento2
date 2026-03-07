<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class AttributeSelectedOption implements TypeResolverInterface
{
    private const TYPE = 'AttributeSelectedOption';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        return self::TYPE;
    }
}
