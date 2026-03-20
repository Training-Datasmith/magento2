<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer concat
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Concat extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $data_arr = [];
        $column = $this->get_column();
        $methods = $column->get_getter() ?: $column->get_index();
        foreach ($methods as $method) {
            if ($column->get_getter() && is_callable([$row, $method]) && substr_compare('get', $method, 1, 3) !== 0) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $data = call_user_func([$row, $method]);
            } else {
                $data = $row->get_data($method);
            }
            if (strlen((string) $data) > 0) {
                $data_arr[] = $data;
            }
        }
        $data = implode($column->get_separator(), $data_arr);
        return $data;
    }
}