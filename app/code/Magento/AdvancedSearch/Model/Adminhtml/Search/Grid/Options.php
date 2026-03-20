<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Adminhtml\Search\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Options implements \Magento\Framework\Option\Array_Interface
{
    public function __construct(protected \Magento\Framework\App\Request_Interface $_request, protected \Magento\Framework\Registry $_registry_manager, protected \Magento\Advanced_Search\Model\Resource_Model\Recommendations $_search_resource_model)
    {
    }
    /**
     * @inheritdoc
     */
    public function to_option_array()
    {
        $queries = $this->_request->get_post('selected_queries');
        $current_query_id = $this->_registry_manager->registry('current_catalog_search')->get_id();
        if ($queries === null && !empty($current_query_id)) {
            return $this->_search_resource_model->get_related_queries($current_query_id);
        }
        return [];
    }
}