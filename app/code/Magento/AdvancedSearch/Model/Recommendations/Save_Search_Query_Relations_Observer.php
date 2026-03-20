<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Recommendations;

use Magento\Advanced_Search\Model\Resource_Model\Recommendations_Factory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\Observer_Interface;
class Save_Search_Query_Relations_Observer implements Observer_Interface
{
    /**
     * @var RecommendationsFactory
     */
    private $recommendations_factory;
    public function __construct(Recommendations_Factory $recommendations_factory)
    {
        $this->recommendations_factory = $recommendations_factory;
    }
    /**
     * Save search query relations after save search query
     */
    public function execute(Event_Observer $observer): void
    {
        $search_query_model = $observer->get_event()->get_data_object();
        $query_id = $search_query_model->get_id();
        $related_queries = $search_query_model->get_selected_queries_grid() ?? '';
        if (strlen($related_queries) == 0) {
            $related_queries = [];
        } else {
            $related_queries = explode('&', $related_queries);
        }
        $this->recommendations_factory->create()->save_related_queries($query_id, $related_queries);
    }
}