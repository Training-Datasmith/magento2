<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

class CalculatedStatusSql
{
    /**
     * Get sql to calculate bulk status
     *
     * @return \Zend_Db_Expr
     */
    public function get(string $operationTableName)
    {
        return new \Zend_Db_Expr(
            '(IF(
                (SELECT count(*)
                    FROM ' . $operationTableName . '
                    WHERE bulk_uuid = main_table.uuid
                ) = 0,
                ' . BulkSummaryInterface::NOT_STARTED . ',
                (SELECT MAX(status) FROM ' . $operationTableName . ' WHERE bulk_uuid = main_table.uuid)
            ))'
        );
    }
}
