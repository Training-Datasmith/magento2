<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\Framework\EntityManager\Operation\CheckIfExistsInterface;

/**
 * CheckIfExists operation for list of bulk operations.
 */
class CheckIfExists implements CheckIfExistsInterface
{
    /**
     * Always returns false because all operations will be saved using insertOnDuplicate query.
     *
     * @param object $entity
     * @param array $arguments
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = []): bool
    {
        return false;
    }
}
