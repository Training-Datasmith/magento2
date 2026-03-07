<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * Class OperationStatusValidator to validate operation status
 */
class OperationStatusValidator
{
    /**
     * OperationStatusValidator constructor.
     */
    public function __construct(private readonly OperationStatusPool $operationStatusPool)
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
        $statuses = $this->operationStatusPool->getStatuses();

        if (!in_array($status, $statuses)) {
            throw new \InvalidArgumentException('Invalid Operation Status.');
        }
    }
}
