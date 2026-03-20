<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

use Magento\Catalog\Controller\Adminhtml\Product;
class Search extends Product
{
    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->get_response()->set_body($this->_view->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search::class)->set_index($this->get_request()->get_param('index'))->set_first_show(true)->to_html());
    }
}