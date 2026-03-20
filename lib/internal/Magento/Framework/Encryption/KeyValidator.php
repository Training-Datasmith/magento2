<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Encryption;

use Magento\Framework\Config\Config_Options_List_Constants;
/**
 * Encryption Key Validator
 */
class Key_Validator
{
    /**
     * Validate encryption key
     *
     * Validate that encryption key is exactly 32 characters long and has
     * no trailing spaces, no invisible characters (tabs, new lines, etc.)
     *
     * @param string $value
     * @return bool
     */
    public function is_valid($value): bool
    {
        if (str_starts_with($value, Config_Options_List_Constants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX)) {
            return (bool) $value && preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value);
        } else {
            return $value && strlen($value) === Config_Options_List_Constants::STORE_KEY_RANDOM_STRING_SIZE && preg_match('/^\S+$/', $value);
        }
    }
}