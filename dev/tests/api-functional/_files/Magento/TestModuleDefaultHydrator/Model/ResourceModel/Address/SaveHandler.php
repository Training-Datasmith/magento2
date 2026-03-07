<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestModuleDefaultHydrator\Model\ResourceModel\Address;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CustomerInterface $entity
     * @param array $arguments
     * @return CustomerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $newAddresses = [];
        foreach ($entity->getAddresses() as $address) {
            $address->setCustomerId($entity->getId());
            $newAddresses[] = $this->entityManager->save($address);
        }
        $entity->setAddresses($newAddresses);
        return $entity;
    }
}
