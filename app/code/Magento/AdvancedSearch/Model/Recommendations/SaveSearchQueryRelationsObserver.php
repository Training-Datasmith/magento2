<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveSearchQueryRelationsObserver implements ObserverInterface
{
    /**
     * @var RecommendationsFactory
     */
    private $recommendationsFactory;

    public function __construct(RecommendationsFactory $recommendationsFactory)
    {
        $this->recommendationsFactory = $recommendationsFactory;
    }

    /**
     * Save search query relations after save search query
     */
    public function execute(EventObserver $observer): void
    {
        $searchQueryModel = $observer->getEvent()->getDataObject();
        $queryId = $searchQueryModel->getId();
        $relatedQueries = $searchQueryModel->getSelectedQueriesGrid() ?? '';

        if (strlen($relatedQueries) == 0) {
            $relatedQueries = [];
        } else {
            $relatedQueries = explode('&', $relatedQueries);
        }

        $this->recommendationsFactory->create()->saveRelatedQueries($queryId, $relatedQueries);
    }
}
