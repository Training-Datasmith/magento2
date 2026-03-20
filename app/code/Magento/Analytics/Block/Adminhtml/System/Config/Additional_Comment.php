<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Provides field with additional information
 */
class Additional_Comment extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $html = '<div class="config-additional-comment-title">' . $element->get_label() . '</div>';
        $html .= '<div class="config-additional-comment-content">' . $element->get_comment() . '</div>';
        return $this->decorate_row_html($element, $html);
    }
    /**
     * Add additional html formatting
     */
    private function decorate_row_html(\Magento\Framework\Data\Form\Element\Abstract_Element $element, string $html): string
    {
        return sprintf('<tr id="row_%s"><td colspan="3"><div class="config-additional-comment">%s</div></td></tr>', $element->get_html_id(), $html);
    }
}