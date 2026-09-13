<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Serialize;

/**
 * Validate JSON string
 */
class JsonValidator
{
    /**
     * Check if string is valid JSON string
     *
     * @param string $string
     * @return bool
     */
    public function isValid($string)
    {
        if (is_int($string) || is_float($string)) {
            $string = (string) $string;
        }
        if (is_string($string) && $string !== '') {
            json_decode($string);
            if (json_last_error() === JSON_ERROR_NONE) {
                return true;
            }
        }
        return false;
    }
}
