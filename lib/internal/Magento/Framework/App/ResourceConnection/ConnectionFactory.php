<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection;

use Magento\Framework\Model\Resource_Model\Type\Db\Connection_Factory as ModelConnectionFactory;
/**
 * Connection adapter factory
 */
class Connection_Factory extends Model_Connection_Factory
{
    /**
     * Create connection adapter instance
     *
     * @param array $connectionConfig
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $connection_config)
    {
        $connection = parent::create($connection_config);
        /** @var \Magento\Framework\DB\Adapter\DdlCache $ddlCache */
        $ddl_cache = $this->object_manager->get(\Magento\Framework\DB\Adapter\Ddl_Cache::class);
        $connection->set_cache_adapter($ddl_cache);
        return $connection;
    }
}