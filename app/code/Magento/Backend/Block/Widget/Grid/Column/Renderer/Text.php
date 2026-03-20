<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Data_Object;
/**
 * Backend grid item renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Text extends Abstract_Renderer
{
    /**
     * Format variables pattern
     *
     * @var string
     */
    protected $_variable_pattern = '/\$([a-z0-9_]+)/i';
    /**
     * Get value for the cel
     *
     * @param DataObject $row
     * @return string
     */
    public function _get_value(Data_Object $row)
    {
        if (null === $this->get_column()->get_format()) {
            return $this->get_simple_value($row);
        }
        return $this->get_formatted_value($row);
    }
    /**
     * Get simple value
     *
     * @param DataObject $row
     * @return string
     */
    private function get_simple_value(Data_Object $row)
    {
        $data = parent::_get_value($row);
        $value = null === $data ? $this->get_column()->get_default() : $data;
        if (true === $this->get_column()->get_translate()) {
            $value = __($value);
        }
        return $this->escape_html($value);
    }
    /**
     * Replace placeholders in the string with values
     *
     * @param DataObject $row
     * @return string
     */
    private function get_formatted_value(Data_Object $row)
    {
        $value = $this->get_column()->get_format() ?: '';
        if (true === $this->get_column()->get_translate()) {
            $value = __($value);
        }
        if ($value && preg_match_all($this->_variable_pattern, $value, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $replacement = $row->get_data($matches[1][$index]);
                $value = str_replace($match, $replacement, $value);
            }
        }
        return $this->escape_html($value);
    }
}