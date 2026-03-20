<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Recommendations;

use Magento\Advanced_Search\Model\Suggested_Queries_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Search\Model\Query_Interface;
use Magento\Store\Model\Scope_Interface;
class Data_Provider implements Suggested_Queries_Interface
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
    private $query_result_factory;
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $search_layer;
    /**
     * @var \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory
     */
    private $recommendations_factory;
    /**
     * DataProvider constructor.
     */
    public function __construct(private readonly Scope_Config_Interface $scope_config, \Magento\Catalog\Model\Layer\Resolver $layer_resolver, \Magento\Advanced_Search\Model\Resource_Model\Recommendations_Factory $recommendations_factory, \Magento\Search\Model\Query_Result_Factory $query_result_factory)
    {
        $this->search_layer = $layer_resolver->get();
        $this->recommendations_factory = $recommendations_factory;
        $this->query_result_factory = $query_result_factory;
    }
    /**
     * Is Results Count Enabled
     *
     * @return bool
     */
    public function is_results_count_enabled()
    {
        return $this->scope_config->is_set_flag(self::CONFIG_RESULTS_COUNT_ENABLED, Scope_Interface::SCOPE_STORE);
    }
    /**
     * @inheritdoc
     * @return mixed[]
     */
    public function get_items(Query_Interface $query): array
    {
        $recommendations = [];
        if (!$this->is_search_recommendations_enabled()) {
            return [];
        }
        foreach ($this->get_search_recommendations($query) as $recommendation) {
            $recommendations[] = $this->query_result_factory->create(['queryText' => $recommendation['query_text'], 'resultsCount' => $recommendation['num_results']]);
        }
        return $recommendations;
    }
    /**
     * Return Search Recommendations
     *
     * @return array
     */
    private function get_search_recommendations(\Magento\Search\Model\Query_Interface $query)
    {
        $recommendations = [];
        if ($this->is_search_recommendations_enabled()) {
            $product_collection = $this->search_layer->get_product_collection();
            $params = ['store_id' => $product_collection->get_store_id()];
            /** @var \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $recommendationsResource */
            $recommendations_resource = $this->recommendations_factory->create();
            $recommendations = $recommendations_resource->get_recommendations_by_query($query->get_query_text(), $params, $this->get_search_recommendations_count());
        }
        return $recommendations;
    }
    /**
     * Is Search Recommendations Enabled
     *
     * @return bool
     */
    private function is_search_recommendations_enabled()
    {
        return $this->scope_config->is_set_flag(self::CONFIG_IS_ENABLED, Scope_Interface::SCOPE_STORE);
    }
    /**
     * Return Search Recommendations Count
     */
    private function get_search_recommendations_count(): int
    {
        return (int) $this->scope_config->get_value(self::CONFIG_RESULTS_COUNT, Scope_Interface::SCOPE_STORE);
    }
}