<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class DbIsolation
{
    /**
     * @param bool $state
     */
    public function __construct(
        public bool $state = true
    ) {
    }
}
