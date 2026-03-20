<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Controller\Adminhtml\Dashboard\Chart;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Backend\Model\Dashboard\Chart;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Json_Factory;
/**
 * Get order amounts chart data controller
 */
class Amounts extends Dashboard implements Http_Post_Action_Interface
{
    /**
     * @var JsonFactory
     */
    private $result_json_factory;
    /**
     * @var Chart
     */
    private $chart;
    /**
     * Amounts constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Chart $chart
     */
    public function __construct(Context $context, Json_Factory $result_json_factory, Chart $chart)
    {
        parent::__construct($context);
        $this->result_json_factory = $result_json_factory;
        $this->chart = $chart;
    }
    /**
     * Get chart data
     *
     * @return Json
     */
    public function execute(): Json
    {
        $data = ['data' => $this->chart->get_by_period($this->_request->get_param('period'), 'revenue', $this->_request->get_param('store'), $this->_request->get_param('website'), $this->_request->get_param('group')), 'label' => __('Revenue')];
        return $this->result_json_factory->create()->set_data($data);
    }
}