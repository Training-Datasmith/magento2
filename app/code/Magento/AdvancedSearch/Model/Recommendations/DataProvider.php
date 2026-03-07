<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Store\Model\ScopeInterface;

class DataProvider implements SuggestedQueriesInterface
{
    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_ENABLED
     */
    public const CONFIG_IS_ENABLED = 'catalog/search/search_recommendations_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED
     */
    public const CONFIG_RESULTS_COUNT_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT
     */
    public const CONFIG_RESULTS_COUNT = 'catalog/search/search_recommendations_count';

    /**
     * @var \Magento\Search\Model\QueryResultFactory
     */
    private $queryResultFactory;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $searchLayer;

    /**
     * @var \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory
     */
    private $recommendationsFactory;

    /**
     * DataProvider constructor.
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory $recommendationsFactory,
        \Magento\Search\Model\QueryResultFactory $queryResultFactory
    ) {
        $this->searchLayer = $layerResolver->get();
        $this->recommendationsFactory = $recommendationsFactory;
        $this->queryResultFactory = $queryResultFactory;
    }

    /**
     * Is Results Count Enabled
     *
     * @return bool
     */
    public function isResultsCountEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_RESULTS_COUNT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     * @return mixed[]
     */
    public function getItems(QueryInterface $query): array
    {
        $recommendations = [];

        if (!$this->isSearchRecommendationsEnabled()) {
            return [];
        }

        foreach ($this->getSearchRecommendations($query) as $recommendation) {
            $recommendations[] = $this->queryResultFactory->create(
                [
                    'queryText' => $recommendation['query_text'],
                    'resultsCount' => $recommendation['num_results'],
                ]
            );
        }
        return $recommendations;
    }

    /**
     * Return Search Recommendations
     *
     * @return array
     */
    private function getSearchRecommendations(\Magento\Search\Model\QueryInterface $query)
    {
        $recommendations = [];

        if ($this->isSearchRecommendationsEnabled()) {
            $productCollection = $this->searchLayer->getProductCollection();
            $params = ['store_id' => $productCollection->getStoreId()];

            /** @var \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $recommendationsResource */
            $recommendationsResource = $this->recommendationsFactory->create();
            $recommendations = $recommendationsResource->getRecommendationsByQuery(
                $query->getQueryText(),
                $params,
                $this->getSearchRecommendationsCount()
            );
        }

        return $recommendations;
    }

    /**
     * Is Search Recommendations Enabled
     *
     * @return bool
     */
    private function isSearchRecommendationsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Search Recommendations Count
     */
    private function getSearchRecommendationsCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_RESULTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
