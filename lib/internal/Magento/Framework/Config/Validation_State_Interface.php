<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config validation state interface.
 *
 * @api
 * @since 100.0.2
 */
interface Validation_State_Interface
{
    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function is_validation_required();
}