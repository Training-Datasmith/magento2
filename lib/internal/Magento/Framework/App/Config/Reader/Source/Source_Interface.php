<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Reader\Source;

/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * @package Magento\Framework\App\Config\Reader\Source
 * @api
 */
interface Source_Interface
{
    /**
     * Retrieve config by scope
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function get($scope_code = null);
}