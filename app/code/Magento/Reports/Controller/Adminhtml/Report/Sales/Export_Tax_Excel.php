<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportTaxExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export tax report grid to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'tax.xml';
        $grid = $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Sales\Tax\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
