<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Adminhtml\Search\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(protected \Magento\Framework\App\RequestInterface $_request, protected \Magento\Framework\Registry $_registryManager, protected \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $_searchResourceModel)
    {
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $queries = $this->_request->getPost('selected_queries');

        $currentQueryId = $this->_registryManager->registry('current_catalog_search')->getId();
        if ($queries === null && !empty($currentQueryId)) {
            return $this->_searchResourceModel->getRelatedQueries($currentQueryId);
        }
        return [];
    }
}
