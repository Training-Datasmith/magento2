<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Checkbox grid column filter
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Return formatted HTML
     *
     * @return string
     */
    public function get_html()
    {
        return '<span class="head-massaction">' . parent::get_html() . '</span>';
    }
    /**
     * Return an array of options
     *
     * @return array
     */
    protected function _get_options()
    {
        return [['label' => __('Any'), 'value' => ''], ['label' => __('Yes'), 'value' => 1], ['label' => __('No'), 'value' => 0]];
    }
    /**
     * Return expression for SQL 'where' clause
     *
     * @return array
     */
    public function get_condition()
    {
        if ($this->get_value()) {
            return $this->get_column()->get_value();
        } else {
            return [['neq' => $this->get_column()->get_value()], ['is' => new \Zend_Db_Expr('NULL')]];
        }
    }
}