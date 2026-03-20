<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

/**
 * Interface IdentityValidatorInterface
 *
 * @api
 */
interface Identity_Validator_Interface
{
    /**
     * Checks if uuid is valid
     *
     * @param string $value
     *
     * @return bool
     */
    public function is_valid(string $value): bool;
}