<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model;

use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Search\Engine_Resolver_Interface;
use Magento\Search\Model\Query_Interface;
class Suggested_Queries implements Suggested_Queries_Interface
{
    /**
     * @var SuggestedQueriesInterface
     */
    private $data_provider;
    /**
     * SuggestedQueries constructor.
     */
    public function __construct(
        private readonly Engine_Resolver_Interface $engine_resolver,
        private readonly Object_Manager_Interface $object_manager,
        /**
         * Array of SuggestedQueriesInterface class names.
         */
        private array $data
    )
    {
    }
    /**
     * @inheritdoc
     */
    public function is_results_count_enabled()
    {
        return $this->get_data_provider()->is_results_count_enabled();
    }
    /**
     * @inheritdoc
     */
    public function get_items(Query_Interface $query)
    {
        return $this->get_data_provider()->get_items($query);
    }
    /**
     * Returns DataProvider for SuggestedQueries
     *
     * @return SuggestedQueriesInterface|SuggestedQueriesInterface[]
     * @throws \Exception
     */
    private function get_data_provider()
    {
        if (empty($this->data_provider)) {
            $current_engine = $this->engine_resolver->get_current_search_engine();
            $this->data_provider = $this->object_manager->create($this->data[$current_engine]);
            if (!$this->data_provider instanceof Suggested_Queries_Interface) {
                throw new \InvalidArgumentException('Data provider must implement \Magento\AdvancedSearch\Model\SuggestedQueriesInterface');
            }
        }
        return $this->data_provider;
    }
}