<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\Observer_Interface;
class Set_Attribute_Tab_Block_Observer implements Observer_Interface
{
    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Catalog
     */
    protected $helper_catalog;
    /**
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     */
    public function __construct(\Magento\Catalog\Helper\Catalog $helper_catalog)
    {
        $this->helper_catalog = $helper_catalog;
    }
    /**
     * Setting attribute tab block for bundle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->get_event()->get_product();
        if ($product->get_type_id() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->helper_catalog->set_attribute_tab_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes::class);
        }
        return $this;
    }
}