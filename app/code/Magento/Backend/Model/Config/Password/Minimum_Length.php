<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Config\Password;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\Localized_Exception;
use Magento\User\Model\User_Validation_Rules;
/**
 * Backend model for admin minimum password length configuration
 */
class Minimum_Length extends Value
{
    /**
     * Validate the minimum password length value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function before_save()
    {
        $value = (int) $this->get_value();
        if ($value < User_Validation_Rules::MIN_PASSWORD_LENGTH) {
            throw new Localized_Exception(__('The minimum admin password length must be at least %1 characters.', User_Validation_Rules::MIN_PASSWORD_LENGTH));
        }
        return parent::before_save();
    }
}