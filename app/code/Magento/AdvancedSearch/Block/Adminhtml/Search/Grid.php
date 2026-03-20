<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Block\Adminhtml\Search;

/**
 * Search query relations edit grid
 *
 * @api
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, protected \Magento\Advanced_Search\Model\Adminhtml\Search\Grid\Options $_options, protected \Magento\Framework\Registry $_registry_manager, protected \Magento\Framework\Json\Helper\Data $json_helper, array $data = [])
    {
        parent::__construct($context, $backend_helper, $data);
        $this->set_default_filter(['query_id_selected' => 1]);
    }
    /**
     *  Retrieve a value from registry by a key
     *
     * @return mixed
     */
    public function get_query()
    {
        return $this->_registry_manager->registry('current_catalog_search');
    }
    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _add_column_filter_to_collection($column): static
    {
        // Set custom filter for query selected flag
        if ($column->get_id() == 'query_id_selected' && $this->get_query()->get_id()) {
            $selected_ids = $this->get_selected_queries();
            if (empty($selected_ids)) {
                $selected_ids = 0;
            }
            if ($column->get_filter()->get_value()) {
                $this->get_collection()->add_field_to_filter('query_id', ['in' => $selected_ids]);
            } elseif (!empty($selected_ids)) {
                $this->get_collection()->add_field_to_filter('query_id', ['nin' => $selected_ids]);
            }
        } else {
            parent::_add_column_filter_to_collection($column);
        }
        return $this;
    }
    /**
     * Retrieve selected related queries from grid
     *
     * @return array
     */
    public function get_selected_queries()
    {
        return $this->_options->to_option_array();
    }
    /**
     * Get queries json
     *
     * @return string
     */
    public function get_queries_json()
    {
        $queries = array_flip($this->get_selected_queries());
        if (!empty($queries)) {
            return $this->json_helper->json_encode($queries);
        }
        return '{}';
    }
}