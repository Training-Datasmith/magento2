<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Validator\Url_Key;

/**
 * Interface UrlKeyValidatorInterface is responsive for validating urlKeys
 */
interface Url_Key_Validator_Interface
{
    /**
     * Validates urlKey
     *
     * @param string $urlKey
     * @return array
     */
    public function validate(string $url_key): array;
}