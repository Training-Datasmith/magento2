<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\Link_Interface;
use Magento\Bundle\Api\Data\Link_Interface_Factory;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\Api\Data_Object_Helper;
/**
 * Retrieve bundle product links service.
 */
class Links_List
{
    /**
     * @var LinkInterfaceFactory
     */
    protected $link_factory;
    /**
     * @var Type
     */
    protected $type;
    /**
     * @var DataObjectHelper
     */
    protected $data_object_helper;
    /**
     * @param LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(Link_Interface_Factory $link_factory, Type $type, Data_Object_Helper $data_object_helper)
    {
        $this->link_factory = $link_factory;
        $this->type = $type;
        $this->data_object_helper = $data_object_helper;
    }
    /**
     * Get Bundle Product Items Data.
     *
     * @param ProductInterface $product
     * @param int $optionId
     * @return LinkInterface[]
     */
    public function get_items(Product_Interface $product, $option_id)
    {
        $selection_collection = $this->type->get_selections_collection([$option_id], $product);
        $product_links = [];
        /** @var \Magento\Catalog\Model\Product $selection */
        foreach ($selection_collection as $selection) {
            $price_type = $product->get_price_type();
            $selection_price_type = $price_type ? $selection->get_selection_price_type() : null;
            $selection_price_value = $selection->get_selection_price_value() < 0 ? $selection->get_price() : $selection->get_selection_price_value();
            $selection_price = $price_type ? $selection_price_value : $selection->get_price();
            /** @var LinkInterface $productLink */
            $product_link = $this->link_factory->create();
            $this->data_object_helper->populate_with_array($product_link, $selection->get_data(), Link_Interface::class);
            $product_link->set_is_default($selection->get_is_default())->set_id($selection->get_selection_id())->set_qty($selection->get_selection_qty())->set_can_change_quantity($selection->get_selection_can_change_qty())->set_price($selection_price)->set_price_type($selection_price_type);
            $product_links[] = $product_link;
        }
        return $product_links;
    }
}