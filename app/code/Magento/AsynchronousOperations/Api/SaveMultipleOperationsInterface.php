<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
/**
 * Interface for saving multiple operations
 *
 * @api
 * @since 100.4.0
 */
interface Save_Multiple_Operations_Interface
{
    /**
     * Save Operations for Bulk
     *
     * @param OperationInterface[] $operations
     * @since 100.4.0
     */
    public function execute(array $operations): void;
}