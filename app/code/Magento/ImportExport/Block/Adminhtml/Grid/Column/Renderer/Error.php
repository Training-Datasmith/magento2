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
class Error extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $result = '';
        if ($row->getData('error_file') != '') {
            $result = '<p> ' . $this->escapeHtml($row->getData('error_file')) .  '</p><a href="'
                . $this->getUrl('*/*/download', ['filename' => $row->getData('error_file')]) . '">'
                . $this->escapeHtml(__('Download'))
                . '</a>';
        }
        return $result;
    }
}
