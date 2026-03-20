<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * MapperInterface
 */
interface Mapper_Interface
{
    /**
     * Map entity field name to database field name
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function entity_to_database($entity_type, $data);
    /**
     * Map database field name to entity field name
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function database_to_entity($entity_type, $data);
}