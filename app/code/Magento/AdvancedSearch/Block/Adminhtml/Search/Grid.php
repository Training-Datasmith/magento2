<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Block\Adminhtml\Search;

/**
 * Search query relations edit grid
 *
 * @api
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        protected \Magento\AdvancedSearch\Model\Adminhtml\Search\Grid\Options $_options,
        protected \Magento\Framework\Registry $_registryManager,
        protected \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->setDefaultFilter(['query_id_selected' => 1]);
    }

    /**
     *  Retrieve a value from registry by a key
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->_registryManager->registry('current_catalog_search');
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column): static
    {
        // Set custom filter for query selected flag
        if ($column->getId() == 'query_id_selected' && $this->getQuery()->getId()) {
            $selectedIds = $this->getSelectedQueries();
            if (empty($selectedIds)) {
                $selectedIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('query_id', ['in' => $selectedIds]);
            } elseif (!empty($selectedIds)) {
                $this->getCollection()->addFieldToFilter('query_id', ['nin' => $selectedIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected related queries from grid
     *
     * @return array
     */
    public function getSelectedQueries()
    {
        return $this->_options->toOptionArray();
    }

    /**
     * Get queries json
     *
     * @return string
     */
    public function getQueriesJson()
    {
        $queries = array_flip($this->getSelectedQueries());
        if (!empty($queries)) {
            return $this->jsonHelper->jsonEncode($queries);
        }
        return '{}';
    }
}
