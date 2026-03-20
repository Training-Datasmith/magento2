<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

use Magento\Catalog\Controller\Adminhtml\Product;
/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Grid extends Product
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $index = $this->get_request()->get_param('index', '');
        if (!preg_match('/^[a-z0-9_.]*$/i', $index)) {
            throw new \InvalidArgumentException('Invalid parameter "index"');
        }
        return $this->get_response()->set_body($this->_view->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class, 'adminhtml.catalog.product.edit.tab.bundle.option.search.grid')->set_index($index)->to_html());
    }
}