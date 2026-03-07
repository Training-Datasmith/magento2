<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\Config;

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
    public function execute(array $configData): array
    {
        if (!isset($configData['config'][0]['report'])) {
            return [];
        }

        $queries = [];
        foreach ($configData['config'][0]['report'] as $queryData) {
            $entityData = array_shift($queryData['source']);
            $queries[$queryData['name']] = $queryData;
            $queries[$queryData['name']]['source'] = $entityData;
        }
        return $queries;
    }
}
