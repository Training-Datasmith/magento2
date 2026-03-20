<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

class Option_Type_List implements \Magento\Bundle\Api\Product_Option_Type_List_Interface
{
    /**
     * @var Source\Option\Type
     */
    protected $types;
    /**
     * @var \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory
     */
    protected $type_factory;
    /**
     * @param Source\Option\Type $type
     * @param \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory $typeFactory
     */
    public function __construct(\Magento\Bundle\Model\Source\Option\Type $type, \Magento\Bundle\Api\Data\Option_Type_Interface_Factory $type_factory)
    {
        $this->types = $type;
        $this->type_factory = $type_factory;
    }
    /**
     * {@inheritdoc}
     */
    public function get_items()
    {
        $option_list = $this->types->to_option_array();
        /** @var \Magento\Bundle\Api\Data\OptionTypeInterface[] $typeList */
        $type_list = [];
        foreach ($option_list as $option) {
            $type_list[] = $this->type_factory->create()->set_code($option['value'])->set_label($option['label']);
        }
        return $type_list;
    }
}