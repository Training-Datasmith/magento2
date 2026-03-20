<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Data_Object;
use Magento\Ui\Component\Listing\Columns\Options as UiOptions;
/**
 * Grid column widget for rendering grid cells that contains mapped values
 *
 * @api
 * @deprecated 100.2.0 Legacy grid renderer; use UI component columns instead.
 * @see UiOptions
 * @since 100.0.2
 */
class Options extends Text
{
    /**
     * Get options from column
     *
     * @return array
     */
    protected function _get_options()
    {
        return $this->get_column()->get_options();
    }
    /**
     * Render a grid cell as options
     *
     * @param DataObject $row
     * @return string|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function render(Data_Object $row)
    {
        $options = $this->_get_options();
        $show_missing_option_values = (bool) $this->get_column()->get_show_missing_option_values();
        if (!empty($options) && is_array($options)) {
            //transform option format
            $output = [];
            foreach ($options as $option) {
                $output[$option['value']] = $option['label'];
            }
            $value = $row->get_data($this->get_column()->get_index());
            if (is_array($value)) {
                $res = [];
                foreach ($value as $item) {
                    if ($item !== null && isset($output[$item])) {
                        $res[] = $this->escape_html($output[$item]);
                    } elseif ($show_missing_option_values) {
                        $res[] = $this->escape_html($item);
                    }
                }
                return implode(', ', $res);
            } elseif ($value !== null && isset($output[$value])) {
                return $this->escape_html($output[$value]);
            } elseif ($value !== null && in_array($value, $output)) {
                return $this->escape_html($value);
            }
        }
    }
}