<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Bulk_Status;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
class Calculated_Status_Sql
{
    /**
     * Get sql to calculate bulk status
     *
     * @return \Zend_Db_Expr
     */
    public function get(string $operation_table_name)
    {
        return new \Zend_Db_Expr('(IF(
                (SELECT count(*)
                    FROM ' . $operation_table_name . '
                    WHERE bulk_uuid = main_table.uuid
                ) = 0,
                ' . Bulk_Summary_Interface::NOT_STARTED . ',
                (SELECT MAX(status) FROM ' . $operation_table_name . ' WHERE bulk_uuid = main_table.uuid)
            ))');
    }
}