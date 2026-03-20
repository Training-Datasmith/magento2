<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Adminhtml\Source;

/**
 * CardinalCommerce Environment Dropdown source
 */
class Environment implements \Magento\Framework\Data\Option_Source_Interface
{
    private const ENVIRONMENT_PRODUCTION = 'production';
    private const ENVIRONMENT_SANDBOX = 'sandbox';
    /**
     * Possible environment types
     *
     * @return array
     */
    public function to_option_array(): array
    {
        return [['value' => self::ENVIRONMENT_SANDBOX, 'label' => 'Sandbox'], ['value' => self::ENVIRONMENT_PRODUCTION, 'label' => 'Production']];
    }
}