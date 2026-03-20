<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Massaction grid column filter
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Checkbox
{
    /**
     * @inheritDoc
     */
    public function get_condition()
    {
        if ($this->get_value()) {
            return ['in' => $this->get_column()->get_selected() ? $this->get_column()->get_selected() : [0]];
        } else {
            return ['nin' => $this->get_column()->get_selected() ? $this->get_column()->get_selected() : [0]];
        }
    }
}