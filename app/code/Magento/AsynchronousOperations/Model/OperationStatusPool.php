<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * Class OperationStatusPool
 *
 * Pool of statuses that require validate
 */
class OperationStatusPool
{
    public function __construct(private readonly array $statuses = [])
    {
    }

    /**
     * Retrieve statuses that require validate
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }
}
