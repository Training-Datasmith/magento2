<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

/**
 * Class OperationStatusValidator to validate operation status
 */
class Operation_Status_Validator
{
    /**
     * OperationStatusValidator constructor.
     */
    public function __construct(private readonly Operation_Status_Pool $operation_status_pool)
    {
    }
    /**
     * Validate method
     *
     * @param int $status
     * @throws \InvalidArgumentException
     */
    public function validate($status): void
    {
        $statuses = $this->operation_status_pool->get_statuses();
        if (!in_array($status, $statuses)) {
            throw new \InvalidArgumentException('Invalid Operation Status.');
        }
    }
}