<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model\Bulk_Status;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Operations_Status_Interface;
use Magento\Asynchronous_Operations\Model\Bulk_Summary;
class Short extends Bulk_Summary implements Bulk_Operations_Status_Interface
{
    /**
     * @inheritDoc
     */
    public function get_operations_list()
    {
        return $this->get_data(self::OPERATIONS_LIST);
    }
    /**
     * @inheritDoc
     */
    public function set_operations_list($operation_status_list)
    {
        return $this->set_data(self::OPERATIONS_LIST, $operation_status_list);
    }
}