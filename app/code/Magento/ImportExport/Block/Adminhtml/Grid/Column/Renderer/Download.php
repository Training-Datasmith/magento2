<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer;

/**
 * Backup grid item renderer
 */
class Download extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        return '<p> ' . $this->escapeHtml($row->getData('imported_file')) .  '</p><a href="'
        . $this->getUrl('*/*/download', ['filename' => $row->getData('imported_file')]) . '">'
        . $this->escapeHtml(__('Download'))
        . '</a>';
    }
}
