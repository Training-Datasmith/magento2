<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Interface DesignResolverInterface
 * @api
 * @since 100.1.0
 */
interface Design_Resolver_Interface extends File_Resolver_Interface
{
    /**
     * Retrieve parent configs
     *
     * @param string $filename
     * @param string $scope
     * @return array
     * @since 100.1.0
     */
    public function get_parents($filename, $scope);
}