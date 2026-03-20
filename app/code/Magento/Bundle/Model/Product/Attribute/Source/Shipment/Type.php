<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product\Attribute\Source\Shipment;

/**
 * Bundle Shipment Type Attribute Renderer
 * @api
 * @since 100.1.0
 */
class Type extends \Magento\Eav\Model\Entity\Attribute\Source\Abstract_Source
{
    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function get_all_options()
    {
        if (null === $this->_options) {
            $this->_options = [['label' => __('Together'), 'value' => 0], ['label' => __('Separately'), 'value' => 1]];
        }
        return $this->_options;
    }
    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function get_option_text($value)
    {
        foreach ($this->get_all_options() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}