<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset;

/**
 * Adminhtml block for fieldset of bundle product
 *
 * @api
 * @since 100.0.2
 */
class Bundle extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{
    /**
     * Returns string with json config for bundle product
     *
     * @return string
     */
    public function get_json_config()
    {
        $options = [];
        $options_array = $this->get_options();
        foreach ($options_array as $option) {
            $option_id = $option->get_id();
            $options[$option_id] = ['id' => $option_id, 'selections' => []];
            foreach ($option->get_selections() as $selection) {
                $options[$option_id]['selections'][$selection->get_selection_id()] = ['can_change_qty' => $selection->get_selection_can_change_qty(), 'default_qty' => $selection->get_selection_qty()];
            }
        }
        $config = ['options' => $options];
        return $this->json_encoder->encode($config);
    }
}