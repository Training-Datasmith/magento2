<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model\Operation;

use Magento\Framework\Entity_Manager\Operation\Check_If_Exists_Interface;
/**
 * CheckIfExists operation for list of bulk operations.
 */
class Check_If_Exists implements Check_If_Exists_Interface
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