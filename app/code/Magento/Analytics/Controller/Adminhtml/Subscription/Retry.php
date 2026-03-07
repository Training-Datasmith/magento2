<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

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
        private readonly SubscriptionHandler $subscriptionHandler
    ) {
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
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $resultRedirect->setPath('adminhtml');
            $this->subscriptionHandler->processEnabled();
        } catch (LocalizedException $e) {
            $this->getMessageManager()->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->getMessageManager()->addExceptionMessage(
                $e,
                __('Sorry, there has been an error processing your request. Please try again later.')
            );
        }

        return $resultRedirect;
    }
}
