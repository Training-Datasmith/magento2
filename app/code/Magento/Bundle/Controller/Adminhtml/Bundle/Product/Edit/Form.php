<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
class Form extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initialization_helper;
    /**
     * @param Action\Context $context
     * @param Product\Builder $productBuilder
     * @param Product\Initialization\Helper $initializationHelper
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, Product\Builder $product_builder, Product\Initialization\Helper $initialization_helper)
    {
        $this->initialization_helper = $initialization_helper;
        parent::__construct($context, $product_builder);
    }
    /**
     * @return void
     */
    public function execute()
    {
        $product = $this->initialization_helper->initialize($this->product_builder->build($this->get_request()));
        $this->get_response()->set_body($this->_view->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle::class, 'admin.product.bundle.items')->set_product_id($product->get_id())->to_html());
    }
}