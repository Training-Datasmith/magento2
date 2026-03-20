<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Catalog\View_Model\Product;

use Magento\Bundle\Model\Product\Single_Choice_Provider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\View_Model\Product\Options_Data as Subject;
/**
 * Plugin to add bundle options data
 */
class Add_Bundle_Options_Data
{
    /**
     * @var SingleChoiceProvider
     */
    private $single_choice_provider;
    /**
     * @param SingleChoiceProvider $singleChoiceProvider
     */
    public function __construct(Single_Choice_Provider $single_choice_provider)
    {
        $this->single_choice_provider = $single_choice_provider;
    }
    public function after_get_options_data(Subject $subject, array $result, Product $product): array
    {
        if ($product->get_type_id() === Type::TYPE_BUNDLE) {
            if ($this->single_choice_provider->is_single_choice_available($product) === true) {
                $type_instance = $product->get_type_instance();
                $type_instance->set_store_filter($product->get_store_id(), $product);
                $options = $type_instance->get_options($product);
                foreach ($options as $option) {
                    $option_id = $option->get_id();
                    $selections_collection = $type_instance->get_selections_collection([$option_id], $product);
                    $selections = $selections_collection->export_to_array();
                    $count_selections = count($selections);
                    foreach ($selections as $selection) {
                        $name = 'bundle_option[' . $option_id . ']';
                        if ($count_selections > 1) {
                            $name .= '[]';
                        }
                        $result[] = ['name' => $name, 'value' => $selection['selection_id']];
                    }
                }
            }
        }
        return $result;
    }
}