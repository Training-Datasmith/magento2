<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Validates report definitions by doing query to storage with limit 0
 */
class ReportValidator
{
    /**
     * ReportValidator constructor.
     *
     * Needs connection and query factory for do a query
     */
    public function __construct(private readonly ConnectionFactory $connectionFactory, private readonly QueryFactory $queryFactory)
    {
    }

    /**
     * Tries to do query for provided report with limit 0 and return error information if it failed
     *
     * @param string $name
     * @param SearchCriteriaInterface $criteria
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($name, ?SearchCriteriaInterface $criteria = null): array
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->connectionFactory->getConnection($query->getConnectionName());
        $query->getSelect()->limit(0);
        try {
            $connection->query($query->getSelect());
        } catch (\Zend_Db_Statement_Exception $e) {
            return [$name, $e->getMessage()];
        }

        return [];
    }
}
