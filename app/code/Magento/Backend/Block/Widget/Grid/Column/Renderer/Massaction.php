<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid widget column renderer massaction
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * @var int
     */
    protected $_default_width = 20;
    /**
     * Render header of the row
     *
     * @return string
     */
    public function render_header()
    {
        return '&nbsp;';
    }
    /**
     * Render HTML properties
     *
     * @return string
     */
    public function render_property()
    {
        $out = parent::render_property();
        $out = preg_replace('/class=".*?"/i', '', $out);
        $out .= ' class="a-center"';
        return $out;
    }
    /**
     * Returns HTML of the object
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        if ($this->get_column()->get_grid()->get_massaction_id_field_only_index_value()) {
            $this->set_no_object_id(true);
        }
        return parent::render($row);
    }
    /**
     * Returns HTML of the checkbox
     *
     * @param string $value
     * @param bool   $checked
     * @return string
     */
    protected function _get_checkbox_html($value, $checked)
    {
        $id = 'id_' . random_int(0, 999);
        $html = '<label class="data-grid-checkbox-cell-inner" for="' . $id . '">';
        $html .= '<input type="checkbox" name="' . $this->get_column()->get_name() . '" ';
        $html .= 'id="' . $id . '" data-role="select-row"';
        $html .= 'value="' . $this->escape_html($value) . '" class="admin__control-checkbox"' . $checked . '/>';
        $html .= '<label for="' . $id . '"></label></label>';
        return $html;
    }
}