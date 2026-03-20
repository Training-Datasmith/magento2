<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Data\Collection;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Search collection provider.
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $factories;

    /**
     * ItemCollectionProvider constructor.
     * @param EngineResolverInterface $engineResolver
     * @param array $factories
     */
    public function __construct(
        EngineResolverInterface $engineResolver,
        array $factories
    ) {
        $this->engineResolver = $engineResolver;
        $this->factories = $factories;
    }

    /**
     * @inheritdoc
     */
    public function getCollection(): Collection
    {
        if (!isset($this->factories[$this->engineResolver->getCurrentSearchEngine()])) {
            return $this->factories['default'];
        }
        return $this->factories[$this->engineResolver->getCurrentSearchEngine()]->create();
    }
}
