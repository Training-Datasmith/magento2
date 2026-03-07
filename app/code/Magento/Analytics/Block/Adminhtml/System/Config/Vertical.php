<?php

declare(strict_types=1);
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
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div class="config-vertical-title">' . $element->getHint() . '</div>';
        $html .= '<div class="config-vertical-comment">' . $element->getComment() . '</div>';
        return $this->decorateRowHtml($element, $html);
    }

    /**
     * Decorates row HTML for custom element style
     */
    private function decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, string $html): string
    {
        $rowHtml = sprintf('<tr><td colspan="4">%s</td></tr>', $html);
        return $rowHtml . sprintf(
            '<tr id="row_%s"><td class="label config-vertical-label">%s</td><td class="value">%s</td></tr>',
            $element->getHtmlId(),
            $element->getLabelHtml($element->getHtmlId(), '[WEBSITE]'),
            $element->getElementHtml()
        );
    }
}
