<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\QueryInterface;

class SuggestedQueries implements SuggestedQueriesInterface
{
    /**
     * @var SuggestedQueriesInterface
     */
    private $dataProvider;

    /**
     * SuggestedQueries constructor.
     */
    public function __construct(
        private readonly EngineResolverInterface $engineResolver,
        private readonly ObjectManagerInterface $objectManager,
        /**
         * Array of SuggestedQueriesInterface class names.
         */
        private array $data
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isResultsCountEnabled()
    {
        return $this->getDataProvider()->isResultsCountEnabled();
    }

    /**
     * @inheritdoc
     */
    public function getItems(QueryInterface $query)
    {
        return $this->getDataProvider()->getItems($query);
    }

    /**
     * Returns DataProvider for SuggestedQueries
     *
     * @return SuggestedQueriesInterface|SuggestedQueriesInterface[]
     * @throws \Exception
     */
    private function getDataProvider()
    {
        if (empty($this->dataProvider)) {
            $currentEngine = $this->engineResolver->getCurrentSearchEngine();
            $this->dataProvider = $this->objectManager->create($this->data[$currentEngine]);
            if (!$this->dataProvider instanceof SuggestedQueriesInterface) {
                throw new \InvalidArgumentException(
                    'Data provider must implement \Magento\AdvancedSearch\Model\SuggestedQueriesInterface'
                );
            }
        }
        return $this->dataProvider;
    }
}
