<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Reports\Controller\Adminhtml\Report\Statistics;
use Psr\Log\Logger_Interface;
/**
 * Refresh Dashboard statistics action.
 */
class Refresh_Statistics extends Statistics implements Http_Post_Action_Interface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param Context $context
     * @param Date $dateFilter
     * @param array $reportTypes
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, Date $date_filter, array $report_types, Logger_Interface $logger)
    {
        parent::__construct($context, $date_filter, $report_types);
        $this->logger = $logger;
    }
    /**
     * Refresh statistics.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collections_names = array_values($this->report_types);
            foreach ($collections_names as $collection_name) {
                $this->_object_manager->create($collection_name)->aggregate();
            }
            $this->message_manager->add_success_message(__('We updated lifetime statistic.'));
        } catch (\Exception $e) {
            $this->message_manager->add_error_message(__('We can\'t refresh lifetime statistics.'));
            $this->logger->critical($e);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path('*/*');
    }
}