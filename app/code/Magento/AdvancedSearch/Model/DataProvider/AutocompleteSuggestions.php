<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Model\DataProvider;

use Magento\AdvancedSearch\Model\SuggestedQueries;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\ScopeInterface;

class AutocompleteSuggestions implements DataProviderInterface
{
    public function __construct(private readonly QueryFactory $queryFactory, private readonly ItemFactory $itemFactory, private readonly ScopeConfig $scopeConfig, private readonly SuggestedQueries $suggestedQueries, private readonly DataProvider $dataProvider)
    {
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        $result = [];
        if ($this->scopeConfig->isSetFlag(
            SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            // populate with search suggestions
            $query = $this->queryFactory->get();
            $suggestions = $this->suggestedQueries->getItems($query);
            foreach ($suggestions as $suggestion) {
                $resultItem = $this->itemFactory->create([
                    'title' => $suggestion->getQueryText(),
                    'num_results' => $suggestion->getResultsCount(),
                ]);
                $result[] = $resultItem;
            }
        } else {
            // populate with autocomplete
            $result = $this->dataProvider->getItems();
        }
        return $result;
    }
}
