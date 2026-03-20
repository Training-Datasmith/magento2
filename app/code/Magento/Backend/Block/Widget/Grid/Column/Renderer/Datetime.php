<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer datetime
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $format = $this->get_column()->get_format();
        $date = $this->_get_value($row);
        if ($date) {
            if (!$date instanceof \DateTimeInterface) {
                $date = new \DateTime($date);
            }
            return $this->_locale_date->format_date_time($date, $format ?: \Intl_Date_Formatter::MEDIUM, $format ?: \Intl_Date_Formatter::MEDIUM, null, $this->get_column()->get_timezone() === false ? 'UTC' : null);
        }
        return $this->get_column()->get_default();
    }
}