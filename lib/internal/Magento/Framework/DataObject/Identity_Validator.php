<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

use Ramsey\Uuid\Uuid;
/**
 * Class IdentityValidator
 *
 * Class for validating Uuid's
 */
class Identity_Validator implements Identity_Validator_Interface
{
    /**
     * @inheritDoc
     */
    public function is_valid(string $value): bool
    {
        $is_valid = Uuid::is_valid($value);
        return $is_valid;
    }
}