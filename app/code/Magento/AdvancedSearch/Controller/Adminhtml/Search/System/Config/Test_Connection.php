<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Search\Controller\Adminhtml\Search\System\Config;

use Magento\Advanced_Search\Model\Client\Client_Resolver;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Json_Factory;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filter\Strip_Tags;
class Test_Connection extends Action implements Http_Post_Action_Interface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Catalog::config_catalog';
    public function __construct(Context $context, private readonly Client_Resolver $client_resolver, private readonly Json_Factory $result_json_factory, private readonly Strip_Tags $tag_filter)
    {
        parent::__construct($context);
    }
    /**
     * Check for connection to server
     *
     * @return Json
     */
    public function execute()
    {
        $result = ['success' => false, 'errorMessage' => ''];
        $options = $this->get_request()->get_params();
        try {
            if (empty($options['engine'])) {
                throw new Localized_Exception(__('Missing search engine parameter.'));
            }
            $response = $this->client_resolver->create($options['engine'], $options)->test_connection();
            if ($response) {
                $result['success'] = true;
            }
        } catch (Localized_Exception $e) {
            $result['errorMessage'] = $e->get_message();
        } catch (\Exception $e) {
            $message = __($e->get_message());
            $result['errorMessage'] = $this->tag_filter->filter($message);
        }
        /** @var Json $resultJson */
        $result_json = $this->result_json_factory->create();
        return $result_json->set_data($result);
    }
}