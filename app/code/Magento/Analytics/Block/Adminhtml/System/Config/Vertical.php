<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Provides vertical select with additional information and style customization
 */
class Vertical extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $html = '<div class="config-vertical-title">' . $element->get_hint() . '</div>';
        $html .= '<div class="config-vertical-comment">' . $element->get_comment() . '</div>';
        return $this->decorate_row_html($element, $html);
    }
    /**
     * Decorates row HTML for custom element style
     */
    private function decorate_row_html(\Magento\Framework\Data\Form\Element\Abstract_Element $element, string $html): string
    {
        $row_html = sprintf('<tr><td colspan="4">%s</td></tr>', $html);
        return $row_html . sprintf('<tr id="row_%s"><td class="label config-vertical-label">%s</td><td class="value">%s</td></tr>', $element->get_html_id(), $element->get_label_html($element->get_html_id(), '[WEBSITE]'), $element->get_element_html());
    }
}