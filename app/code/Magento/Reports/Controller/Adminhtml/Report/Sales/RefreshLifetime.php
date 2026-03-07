<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

class RefreshLifetime extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh statistics for all period
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('refreshLifetime', 'report_statistics');
    }
}
