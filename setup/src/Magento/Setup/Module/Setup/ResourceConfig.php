<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Setup;

/**
 * Simplified resource config for Setup tools
 */
class ResourceConfig implements \Magento\Framework\App\ResourceConnection\ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConnectionName($resourceName)
    {
        return \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
    }
}
