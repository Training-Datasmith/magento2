<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Config\Session_Lifetime;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Backend model for the admin/security/session_lifetime configuration field. Validates session lifetime.
 * @api
 * @since 100.1.0
 */
class Backend_Model extends Value
{
    /** Maximum admin session lifetime; 1 year*/
    public const MAX_LIFETIME = 31536000;
    /** Minimum admin session lifetime */
    public const MIN_LIFETIME = 60;
    /**
     * Processing object before save data
     *
     * @since 100.1.0
     * @throws LocalizedException
     */
    public function before_save()
    {
        $value = (int) $this->get_value();
        if ($value > self::MAX_LIFETIME) {
            throw new Localized_Exception(__('The Admin session lifetime is invalid. ' . 'Set the lifetime to 31536000 seconds (one year) or shorter and try again.'));
        } elseif ($value < self::MIN_LIFETIME) {
            throw new Localized_Exception(__('The Admin session lifetime is invalid. Set the lifetime to 60 seconds or longer and try again.'));
        }
        return parent::before_save();
    }
}