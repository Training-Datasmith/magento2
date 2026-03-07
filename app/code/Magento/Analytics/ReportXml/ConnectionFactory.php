<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;

/**
 * Creates connection instance for export according to existing one
 *
 * This connection does not use buffered statement, also this connection is not persistent
 */
class ConnectionFactory
{
    public function __construct(private readonly ResourceConfigInterface $resourceConfig, private readonly DeploymentConfig $deploymentConfig, private readonly ConnectionFactoryInterface $connectionFactory)
    {
    }

    /**
     * Creates one-time connection for export
     *
     * @param string $resourceName
     * @return AdapterInterface
     */
    public function getConnection($resourceName)
    {
        $connectionName = $this->resourceConfig->getConnectionName($resourceName);
        $configData = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connectionName
        );
        $configData['use_buffered_query'] = false;
        unset($configData['persistent']);

        return $this->connectionFactory->create($configData);
    }
}
