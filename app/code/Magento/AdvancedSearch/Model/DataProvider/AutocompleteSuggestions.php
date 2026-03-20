<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Search\Model\Data_Provider;

use Magento\Advanced_Search\Model\Suggested_Queries;
use Magento\Advanced_Search\Model\Suggested_Queries_Interface;
use Magento\Catalog_Search\Model\Autocomplete\Data_Provider;
use Magento\Framework\App\Config\Scope_Config_Interface as ScopeConfig;
use Magento\Search\Model\Autocomplete\Data_Provider_Interface;
use Magento\Search\Model\Autocomplete\Item_Factory;
use Magento\Search\Model\Query_Factory;
use Magento\Store\Model\Scope_Interface;
class Autocomplete_Suggestions implements Data_Provider_Interface
{
    public function __construct(private readonly Query_Factory $query_factory, private readonly Item_Factory $item_factory, private readonly Scope_Config $scope_config, private readonly Suggested_Queries $suggested_queries, private readonly Data_Provider $data_provider)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_items()
    {
        $result = [];
        if ($this->scope_config->is_set_flag(Suggested_Queries_Interface::SEARCH_SUGGESTION_ENABLED, Scope_Interface::SCOPE_STORE)) {
            // populate with search suggestions
            $query = $this->query_factory->get();
            $suggestions = $this->suggested_queries->get_items($query);
            foreach ($suggestions as $suggestion) {
                $result_item = $this->item_factory->create(['title' => $suggestion->get_query_text(), 'num_results' => $suggestion->get_results_count()]);
                $result[] = $result_item;
            }
        } else {
            // populate with autocomplete
            $result = $this->data_provider->get_items();
        }
        return $result;
    }
}