<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

use Magento\Analytics\Report_Xml\Connection_Factory;
use Magento\Analytics\Report_Xml\Query_Factory;
use Magento\Framework\Api\Search_Criteria_Interface;
/**
 * Validates report definitions by doing query to storage with limit 0
 */
class Report_Validator
{
    /**
     * ReportValidator constructor.
     *
     * Needs connection and query factory for do a query
     */
    public function __construct(private readonly Connection_Factory $connection_factory, private readonly Query_Factory $query_factory)
    {
    }
    /**
     * Tries to do query for provided report with limit 0 and return error information if it failed
     *
     * @param string $name
     * @param SearchCriteriaInterface $criteria
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($name, ?Search_Criteria_Interface $criteria = null): array
    {
        $query = $this->query_factory->create($name);
        $connection = $this->connection_factory->get_connection($query->get_connection_name());
        $query->get_select()->limit(0);
        try {
            $connection->query($query->get_select());
        } catch (\Zend_Db_Statement_Exception $e) {
            return [$name, $e->get_message()];
        }
        return [];
    }
}