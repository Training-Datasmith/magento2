<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Controller\Adminhtml\Reports;

use Magento\Analytics\Model\Exception\State\Subscription_Update_Exception;
use Magento\Analytics\Model\Report_Url_Provider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Provide redirect to resource with reports.
 */
class Show extends Action implements Http_Get_Action_Interface
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magento_Analytics::advanced_reporting';
    public function __construct(Context $context, private readonly Report_Url_Provider $report_url_provider)
    {
        parent::__construct($context);
    }
    /**
     * Redirect to resource with reports.
     *
     * @return Redirect $resultRedirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        try {
            $result_redirect->set_url($this->report_url_provider->get_url());
        } catch (Subscription_Update_Exception $e) {
            $this->get_message_manager()->add_notice_message($e->get_message());
            $result_redirect->set_path('adminhtml');
        } catch (Localized_Exception $e) {
            $this->get_message_manager()->add_exception_message($e, $e->get_message());
            $result_redirect->set_path('adminhtml');
        } catch (\Exception $e) {
            $this->get_message_manager()->add_exception_message($e, __('Sorry, there has been an error processing your request. Please try again later.'));
            $result_redirect->set_path('adminhtml');
        }
        return $result_redirect;
    }
}