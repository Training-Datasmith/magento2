<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Cart\Buy_Request;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Stdlib\Array_Manager;
use Magento\Framework\Stdlib\Array_Manager_Factory;
use Magento\Quote_Graph_Ql\Model\Cart\Buy_Request\Buy_Request_Data_Provider_Interface;
/**
 * Data provider for bundle product buy requests
 */
class Bundle_Data_Provider implements Buy_Request_Data_Provider_Interface
{
    /**
     * @var ArrayManagerFactory
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Array_Manager_Factory $array_manager_factory;
    /**
     * @param ArrayManager $arrayManager @deprecated @see $arrayManagerFactory
     * @param ArrayManagerFactory|null $arrayManagerFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Array_Manager $array_manager, ?Array_Manager_Factory $array_manager_factory = null)
    {
        $this->array_manager_factory = $array_manager_factory ?? Object_Manager::get_instance()->get(Array_Manager_Factory::class);
    }
    /**
     * @inheritdoc
     */
    public function execute(array $cart_item_data): array
    {
        $bundle_options = [];
        $bundle_inputs = $this->array_manager_factory->create()->get('bundle_options', $cart_item_data) ?? [];
        foreach ($bundle_inputs as $bundle_input) {
            $bundle_options['bundle_option'][$bundle_input['id']] = $bundle_input['value'];
            $bundle_options['bundle_option_qty'][$bundle_input['id']] = $bundle_input['quantity'];
        }
        return $bundle_options;
    }
}