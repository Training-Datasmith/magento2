<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Exception;
use Laminas\Http\Request;
use Magento\Backend\App\Action;
use Magento\Backend\Block\Dashboard\Graph;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Backend\Helper\Dashboard\Data;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\Raw_Factory;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\HTTP\Laminas_Client;
use Psr\Log\Logger_Interface;
/**
 * Dashboard graph image tunnel
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.chart.amounts and dashboard.chart.orders in adminhtml_dashboard_index.xml
 */
class Tunnel extends Dashboard implements Http_Get_Action_Interface
{
    /**
     * @var RawFactory
     */
    protected $result_raw_factory;
    /**
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Action\Context $context, Result\Raw_Factory $result_raw_factory)
    {
        parent::__construct($context);
        $this->result_raw_factory = $result_raw_factory;
    }
    /**
     * Forward request for a graph image to the web-service
     *
     * This is done in order to include the image to a HTTPS-page regardless of web-service settings
     *
     * @return  Raw
     */
    public function execute()
    {
        $error = __('invalid request');
        $http_code = 400;
        $ga_data = $this->_request->get_param('ga');
        $ga_hash = $this->_request->get_param('h');
        /** @var Raw $resultRaw */
        $result_raw = $this->result_raw_factory->create();
        if ($ga_data && $ga_hash) {
            /** @var $helper Data */
            $helper = $this->_object_manager->get(Data::class);
            $new_hash = $helper->get_chart_data_hash($ga_data);
            if (Security::compare_strings($new_hash, $ga_hash)) {
                $params = null;
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $params_json = base64_decode(urldecode($ga_data));
                if ($params_json) {
                    $params = json_decode($params_json, true);
                }
                if ($params) {
                    try {
                        $http_client = $this->_object_manager->create(Laminas_Client::class);
                        $http_client->set_uri(Graph::API_URL);
                        $http_client->set_parameter_get($params);
                        $http_client->set_options(['timeout' => 5]);
                        $http_client->set_method(Request::METHOD_GET);
                        $response = $http_client->send();
                        $headers = $response->get_headers()->to_array();
                        $result_raw->set_header('Content-type', $headers['Content-type'])->set_contents($response->get_body());
                        return $result_raw;
                    } catch (Exception $e) {
                        $this->_object_manager->get(Logger_Interface::class)->critical($e);
                        $error = __('see error log for details');
                        $http_code = 503;
                    }
                }
            }
        }
        $result_raw->set_header('Content-Type', 'text/plain; charset=UTF-8')->set_http_response_code($http_code)->set_contents(__('Service unavailable: %1', $error));
        return $result_raw;
    }
}