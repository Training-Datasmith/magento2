<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

/**
 * Interface AttributeInterface
 */
interface Attribute_Interface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     */
    public function execute($entity_type, $entity_data, $arguments = []);
}