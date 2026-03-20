<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Get options for filter value
     *
     * @return array
     */
    protected function _get_options()
    {
        $empty_option = ['value' => null, 'label' => ''];
        $option_groups = $this->get_column()->get_option_groups();
        if ($option_groups) {
            array_unshift($option_groups, $empty_option);
            return $option_groups;
        }
        $col_options = $this->get_column()->get_options();
        if (!empty($col_options) && is_array($col_options)) {
            $options = [$empty_option];
            foreach ($col_options as $value => $label) {
                $options[] = ['value' => $value, 'label' => $label];
            }
            return $options;
        }
        return [];
    }
}