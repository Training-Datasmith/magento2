<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer number
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var int
     */
    protected $_default_width = 100;
    /**
     * Returns value of the row
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     */
    protected function _get_value(\Magento\Framework\Data_Object $row)
    {
        $data = parent::_get_value($row);
        if ($data !== null) {
            $value = $data * 1;
            $sign = (bool) (int) $this->get_column()->get_show_number_sign() && $value > 0 ? '+' : '';
            if ($sign) {
                $value = $sign . $value;
            }
            // fixed for showing zero in grid
            return $value ? $value : '0';
        }
        return $this->get_column()->get_default();
    }
    /**
     * Renders CSS
     *
     * @return string
     */
    public function render_css()
    {
        return parent::render_css() . ' col-number';
    }
}