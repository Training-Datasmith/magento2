<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Data_Object;
/**
 * Produce html output using the given data source.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * Backend grid item abstract renderer
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class Abstract_Renderer extends \Magento\Backend\Block\Abstract_Block implements Renderer_Interface
{
    /**
     * @var int
     */
    protected $_default_width;
    /**
     * @var Column
     */
    protected $_column;
    /**
     * Set column for renderer.
     *
     * @param Column $column
     * @return $this
     */
    public function set_column($column)
    {
        $this->_column = $column;
        return $this;
    }
    /**
     * Returns row associated with the renderer.
     *
     * @return Column
     */
    public function get_column()
    {
        return $this->_column;
    }
    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(Data_Object $row)
    {
        if ($this->get_column()->get_editable()) {
            $result = '<div class="admin__grid-control">';
            $result .= $this->get_column()->get_edit_only() ? '' : '<span class="admin__grid-control-value">' . $this->_get_value($row) . '</span>';
            return $result . $this->_get_input_value_element($row) . '</div>';
        }
        return $this->_get_value($row);
    }
    /**
     * Render column for export
     *
     * @param DataObject $row
     * @return string
     */
    public function render_export(Data_Object $row)
    {
        return $this->render($row);
    }
    /**
     * Returns value of the row.
     *
     * @param DataObject $row
     * @return mixed
     */
    protected function _get_value(Data_Object $row)
    {
        if ($getter = $this->get_column()->get_getter()) {
            if (is_string($getter)) {
                return $row->{$getter}();
            } elseif (is_callable($getter)) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                return call_user_func($getter, $row);
            }
            return '';
        }
        return $this->get_column()->get_index() !== null ? $row->get_data($this->get_column()->get_index()) : null;
    }
    /**
     * Get pre-rendered input element.
     *
     * @param DataObject $row
     * @return string
     */
    public function _get_input_value_element(Data_Object $row)
    {
        return '<input type="text" class="input-text ' . $this->get_column()->get_validate_class() . '" name="' . $this->get_column()->get_id() . '" value="' . $this->_get_input_value($row) . '"/>';
    }
    /**
     * Get input value by row.
     *
     * @param DataObject $row
     * @return mixed
     */
    protected function _get_input_value(Data_Object $row)
    {
        return $this->_get_value($row);
    }
    /**
     * Renders header of the column,
     *
     * @return string
     */
    public function render_header()
    {
        if (false !== $this->get_column()->get_sortable()) {
            $class_name = 'not-sort';
            $dir = is_string($this->get_column()->get_dir()) ? strtolower($this->get_column()->get_dir()) : '';
            $n_dir = $dir == 'asc' ? 'desc' : 'asc';
            if ($dir) {
                $class_name = '_' . $dir . 'end';
            }
            $out = '<th data-sort="' . $this->get_column()->get_id() . '" data-direction="' . $n_dir . '" class="data-grid-th _sortable ' . $class_name . ' ' . $this->get_column()->get_header_css_class() . '"><span>' . $this->get_column()->get_header() . '</span></th>';
        } else {
            $out = '<th class="data-grid-th ' . $this->get_column()->get_header_css_class() . '"><span>' . $this->get_column()->get_header() . '</span></th>';
        }
        return $out;
    }
    /**
     * Render HTML properties.
     *
     * @return string
     */
    public function render_property()
    {
        $out = '';
        $width = $this->_default_width;
        if ($this->get_column()->has_data('width')) {
            $custom_width = $this->get_column()->get_data('width');
            if (null === $custom_width || preg_match('/^[0-9]+%?$/', $custom_width)) {
                $width = $custom_width;
            } elseif (preg_match('/^([0-9]+)px$/', $custom_width, $matches)) {
                $width = (int) $matches[1];
            }
        }
        if (null !== $width) {
            $out .= ' width="' . $width . '"';
        }
        return $out;
    }
    /**
     * Returns HTML for CSS.
     *
     * @return string
     */
    public function render_css()
    {
        return $this->get_column()->get_css_class();
    }
}