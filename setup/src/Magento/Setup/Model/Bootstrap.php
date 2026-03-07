<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Bootstrap as MagentoAppBootstrap;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /**
     * Creates instance of object manager factory
     *
     * @param string $rootDir
     * @param array $initParams
     * @return ObjectManagerFactory
     */
    public function createObjectManagerFactory($rootDir, array $initParams)
    {
        return MagentoAppBootstrap::createObjectManagerFactory($rootDir, $initParams);
    }
}
