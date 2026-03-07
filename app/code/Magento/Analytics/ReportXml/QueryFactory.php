<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Analytics\ReportXml\DB\SelectBuilderFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Creates Query object according to configuration
 *
 * Factory for @see \Magento\Analytics\ReportXml\Query
 */
class QueryFactory
{
    /**
     * QueryFactory constructor.
     */
    public function __construct(private readonly CacheInterface $queryCache, private readonly SelectHydrator $selectHydrator, private readonly ObjectManagerInterface $objectManager, private readonly SelectBuilderFactory $selectBuilderFactory, private readonly Config $config, private readonly array $assemblers, private readonly Json $jsonSerializer)
    {
    }

    /**
     * Returns query connection name according to configuration
     *
     * @param string $queryConfig
     */
    private function getQueryConnectionName($queryConfig): string
    {
        return $queryConfig['connection'] ?? 'default';
    }

    /**
     * Create query according to configuration settings
     *
     * @param string $queryName
     * @return Query
     */
    private function constructQuery($queryName)
    {
        $queryConfig = $this->config->get($queryName);
        $selectBuilder = $this->selectBuilderFactory->create();
        $selectBuilder->setConnectionName($this->getQueryConnectionName($queryConfig));
        foreach ($this->assemblers as $assembler) {
            $selectBuilder = $assembler->assemble($selectBuilder, $queryConfig);
        }
        $select = $selectBuilder->create();
        return $this->createQueryObject(
            $select,
            $selectBuilder->getConnectionName(),
            $queryConfig
        );
    }

    /**
     * Creates query by name
     *
     * @param string $queryName
     * @return Query
     */
    public function create($queryName)
    {
        $cached = $this->queryCache->load($queryName);
        if ($cached) {
            $queryData = $this->jsonSerializer->unserialize($cached);
            return $this->createQueryObject(
                $this->selectHydrator->recreate($queryData['select_parts']),
                $queryData['connectionName'],
                $queryData['config']
            );
        }
        $query = $this->constructQuery($queryName);
        $this->queryCache->save(
            $this->jsonSerializer->serialize($query),
            $queryName
        );
        return $query;
    }

    /**
     * Create query class using objectmanger
     *
     * @return Query
     */
    private function createQueryObject(
        Select $select,
        string $connection,
        array $queryConfig
    ) {
        return $this->objectManager->create(
            Query::class,
            [
                'select' => $select,
                'selectHydrator' => $this->selectHydrator,
                'connectionName' => $connection,
                'config' => $queryConfig,
            ]
        );
    }
}
