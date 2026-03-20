<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Advanced;

use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Strategy provider for preparing product collection.
 */
class ProductCollectionPrepareStrategyProvider
{
    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $strategies;

    /**
     * @param EngineResolverInterface $engineResolver
     * @param array $strategies
     */
    public function __construct(
        EngineResolverInterface $engineResolver,
        array $strategies
    ) {
        $this->engineResolver = $engineResolver;
        $this->strategies = $strategies;
    }

    /**
     * Get strategy provider for product collection prepare process.
     *
     * @return ProductCollectionPrepareStrategyInterface
     * @throws RuntimeException
     */
    public function getStrategy(): ProductCollectionPrepareStrategyInterface
    {
        if (!isset($this->strategies[$this->engineResolver->getCurrentSearchEngine()])) {
            if ($this->strategies['default']) {
                return $this->strategies['default'];
            } else {
                throw new RuntimeException(__('Default product collection strategy not found'));
            }
        }
        return $this->strategies[$this->engineResolver->getCurrentSearchEngine()];
    }
}
