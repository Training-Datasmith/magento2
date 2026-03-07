<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @api
 * @since 100.0.2
 */
interface ScopedAttributeInterface
{
    public const SCOPE_STORE = 0;

    public const SCOPE_GLOBAL = 1;

    public const SCOPE_WEBSITE = 2;
}
