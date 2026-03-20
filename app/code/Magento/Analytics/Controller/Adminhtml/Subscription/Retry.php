<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Retry subscription to Magento BI Advanced Reporting.
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Retry extends Action
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magento_Analytics::analytics_settings';
    public function __construct(
        Context $context,
        /**
         * Resource for managing subscription to Magento Analytics.
         */
        private readonly Subscription_Handler $subscription_handler
    )
    {
        parent::__construct($context);
    }
    /**
     * Retry process of subscription.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        try {
            $result_redirect->set_path('adminhtml');
            $this->subscription_handler->process_enabled();
        } catch (Localized_Exception $e) {
            $this->get_message_manager()->add_exception_message($e, $e->get_message());
        } catch (\Exception $e) {
            $this->get_message_manager()->add_exception_message($e, __('Sorry, there has been an error processing your request. Please try again later.'));
        }
        return $result_redirect;
    }
}