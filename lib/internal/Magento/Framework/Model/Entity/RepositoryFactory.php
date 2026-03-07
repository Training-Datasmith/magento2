<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class RepositoryFactory
 */
class RepositoryFactory
{
    /**
     * List of entity types and their repositories
     *
     * @var array
     */
    protected $entities;

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * RepositoryFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $entities
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $entities = []
    ) {
        $this->objectManager = $objectManager;
        $this->entities = $entities;
    }

    /**
     * @param string $entityType
     * @return object
     * @throws NotFoundException
     */
    public function create($entityType)
    {
        if (!isset($this->entities[$entityType])) {
            $message =
                sprintf('The repository for the "%s" entity type isn\'t declared. Verify and try again.', $entityType);
            throw new NotFoundException(new \Magento\Framework\Phrase($message));
        }
        return $this->objectManager->get($this->entities[$entityType]);
    }
}
