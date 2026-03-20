<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Longtext extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Render contents as a long text
     *
     * Text will be truncated as specified in string_limit, truncate or 250 by default
     * Also it can be html-escaped and nl2br()
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $truncate_length = 250;
        // stringLength() is for legacy purposes
        if ($this->get_column()->get_string_limit()) {
            $truncate_length = $this->get_column()->get_string_limit();
        }
        if ($this->get_column()->get_truncate()) {
            $truncate_length = $this->get_column()->get_truncate();
        }
        $text = $this->filter_manager->truncate(parent::_get_value($row), ['length' => $truncate_length]);
        if (!$this->get_column()->has_escape() || $this->get_column()->get_escape()) {
            $text = $this->escape_html($text);
        }
        if ($this->get_column()->get_nl2br()) {
            $text = nl2br($text);
        }
        return $text;
    }
}