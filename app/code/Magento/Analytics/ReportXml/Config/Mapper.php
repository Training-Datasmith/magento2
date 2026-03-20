<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\Config;

/**
 * A reports configuration mapper.
 *
 * Transforms configuration data to improve its usability.
 *
 * @see usage examples in \Magento\Analytics\ReportXml\Config\Reader
 */
class Mapper
{
    /**
     * Transforms configuration data.
     */
    public function execute(array $config_data): array
    {
        if (!isset($config_data['config'][0]['report'])) {
            return [];
        }
        $queries = [];
        foreach ($config_data['config'][0]['report'] as $query_data) {
            $entity_data = array_shift($query_data['source']);
            $queries[$query_data['name']] = $query_data;
            $queries[$query_data['name']]['source'] = $entity_data;
        }
        return $queries;
    }
}