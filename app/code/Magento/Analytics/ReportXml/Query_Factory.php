<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Analytics\Report_Xml\DB\Select_Builder_Factory;
use Magento\Framework\App\Cache_Interface;
use Magento\Framework\DB\Select;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Creates Query object according to configuration
 *
 * Factory for @see \Magento\Analytics\ReportXml\Query
 */
class Query_Factory
{
    /**
     * QueryFactory constructor.
     */
    public function __construct(private readonly Cache_Interface $query_cache, private readonly Select_Hydrator $select_hydrator, private readonly Object_Manager_Interface $object_manager, private readonly Select_Builder_Factory $select_builder_factory, private readonly Config $config, private readonly array $assemblers, private readonly Json $json_serializer)
    {
    }
    /**
     * Returns query connection name according to configuration
     *
     * @param string $queryConfig
     */
    private function get_query_connection_name($query_config): string
    {
        return $query_config['connection'] ?? 'default';
    }
    /**
     * Create query according to configuration settings
     *
     * @param string $queryName
     * @return Query
     */
    private function construct_query($query_name)
    {
        $query_config = $this->config->get($query_name);
        $select_builder = $this->select_builder_factory->create();
        $select_builder->set_connection_name($this->get_query_connection_name($query_config));
        foreach ($this->assemblers as $assembler) {
            $select_builder = $assembler->assemble($select_builder, $query_config);
        }
        $select = $select_builder->create();
        return $this->create_query_object($select, $select_builder->get_connection_name(), $query_config);
    }
    /**
     * Creates query by name
     *
     * @param string $queryName
     * @return Query
     */
    public function create($query_name)
    {
        $cached = $this->query_cache->load($query_name);
        if ($cached) {
            $query_data = $this->json_serializer->unserialize($cached);
            return $this->create_query_object($this->select_hydrator->recreate($query_data['select_parts']), $query_data['connectionName'], $query_data['config']);
        }
        $query = $this->construct_query($query_name);
        $this->query_cache->save($this->json_serializer->serialize($query), $query_name);
        return $query;
    }
    /**
     * Create query class using objectmanger
     *
     * @return Query
     */
    private function create_query_object(Select $select, string $connection, array $query_config)
    {
        return $this->object_manager->create(Query::class, ['select' => $select, 'selectHydrator' => $this->select_hydrator, 'connectionName' => $connection, 'config' => $query_config]);
    }
}