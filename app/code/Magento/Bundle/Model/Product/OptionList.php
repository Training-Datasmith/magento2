<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product;

class Option_List
{
    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    protected $option_factory;
    /**
     * @var Type
     */
    protected $type;
    /**
     * @var LinksList
     */
    protected $link_list;
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $data_object_helper;
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extension_attributes_join_processor;
    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param LinksList $linkList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(\Magento\Bundle\Model\Product\Type $type, \Magento\Bundle\Api\Data\Option_Interface_Factory $option_factory, \Magento\Bundle\Model\Product\Links_List $link_list, \Magento\Framework\Api\Data_Object_Helper $data_object_helper, \Magento\Framework\Api\Extension_Attribute\Join_Processor_Interface $extension_attributes_join_processor)
    {
        $this->type = $type;
        $this->option_factory = $option_factory;
        $this->link_list = $link_list;
        $this->data_object_helper = $data_object_helper;
        $this->extension_attributes_join_processor = $extension_attributes_join_processor;
    }
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function get_items(\Magento\Catalog\Api\Data\Product_Interface $product)
    {
        $option_collection = $this->type->get_options_collection($product);
        $this->extension_attributes_join_processor->process($option_collection);
        $option_list = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($option_collection as $option) {
            $product_links = $this->link_list->get_items($product, $option->get_option_id());
            /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
            $option_data_object = $this->option_factory->create();
            $this->data_object_helper->populate_with_array($option_data_object, $option->get_data(), \Magento\Bundle\Api\Data\Option_Interface::class);
            $option_data_object->set_option_id($option->get_option_id())->set_title($option->get_title() === null ? $option->get_default_title() : $option->get_title())->set_default_title($option->get_default_title())->set_sku($product->get_sku())->set_product_links($product_links);
            $option_list[] = $option_data_object;
        }
        return $option_list;
    }
}