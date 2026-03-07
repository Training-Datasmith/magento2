<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Reports\Controller\Adminhtml\Index as ReportsIndexController;

class Report extends ReportsIndexController implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Reports::report_search';

    /**
     * Search terms report action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_eventManager->dispatch('on_view_report', ['report' => 'search']);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Search::report_search_term')
            ->addBreadcrumb(__('Reports'), __('Reports'))
            ->addBreadcrumb(__('Search Terms'), __('Search Terms'));
        $resultPage->getConfig()->getTitle()->prepend(__('Search Terms Report'));
        return $resultPage;
    }
}
