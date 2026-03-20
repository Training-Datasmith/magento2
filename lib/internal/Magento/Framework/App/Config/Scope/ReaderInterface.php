<?php

declare (strict_types=1);
/**
 * Scope Reader
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Scope;

/**
 * Interface \Magento\Framework\App\Config\Scope\ReaderInterface
 *
 * @api
 */
interface Reader_Interface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scopeType
     * @throws \Exception May throw an exception if the given scope is invalid
     * @return array
     */
    public function read($scope_type = null);
}