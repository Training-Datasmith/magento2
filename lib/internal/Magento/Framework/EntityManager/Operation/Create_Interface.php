<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\Entity_Manager\Operation_Interface;
/**
 * Interface for creating entity
 */
interface Create_Interface extends Operation_Interface
{
    /**
     * Create entity
     *
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \Exception
     */
    public function execute($entity, $arguments = []);
}