<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * Class AbstractModelHydrator
 */
class Abstract_Model_Hydrator implements Hydrator_Interface
{
    /**
     * {@inheritdoc}
     */
    public function extract($entity)
    {
        return $entity->get_data();
    }
    /**
     * {@inheritdoc}
     */
    public function hydrate($entity, array $data)
    {
        $entity->set_data(array_merge($entity->get_data(), $data));
        return $entity;
    }
}