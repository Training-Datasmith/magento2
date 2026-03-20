<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Controller;

/**
 * Declarations of core registry keys used by the Tax module
 *
 */
class RegistryConstants
{
    /**
     * Registry key where current tax ID is stored
     */
    public const CURRENT_TAX_RATE_ID = 'current_tax_rate_id';

    /**
     * Registry key where current tax rate form data is stored
     */
    public const CURRENT_TAX_RATE_FORM_DATA = 'current_tax_rate_form_data';
}
