<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

/**
 * Class OperationStatusPool
 *
 * Pool of statuses that require validate
 */
class Operation_Status_Pool
{
    public function __construct(private readonly array $statuses = [])
    {
    }
    /**
     * Retrieve statuses that require validate
     *
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }
}