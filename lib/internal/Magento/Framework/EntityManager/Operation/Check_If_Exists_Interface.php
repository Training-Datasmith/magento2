<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\Entity_Manager\Operation_Interface;
/**
 * Interface for checking if entity exists
 */
interface Check_If_Exists_Interface extends Operation_Interface
{
    /**
     * Check if entity exists
     *
     * @param object $entity
     * @return bool
     * @throws \Exception
     */
    public function execute($entity);
}