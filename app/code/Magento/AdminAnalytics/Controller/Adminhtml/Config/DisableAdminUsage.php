<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Controller\Adminhtml\Config;

use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Backend\App\Action;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller to record Admin analytics usage log
 */
class DisableAdminUsage extends Action implements HttpPostActionInterface
{
    /**
     * DisableAdminUsage constructor.
     */
    public function __construct(
        Action\Context $context,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly NotificationLogger $notificationLogger,
        private readonly Factory $configFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Change the value of config/admin/usage/enabled
     */
    private function disableAdminUsage(): void
    {
        $configModel = $this->configFactory->create();
        $configModel->setDataByPath('admin/usage/enabled', 0);
        $configModel->save();
    }

    /**
     * Log information about the last admin usage selection
     */
    private function markUserNotified(): ResultInterface
    {
        $responseContent = [
            'success' => $this->notificationLogger->log(
                $this->productMetadata->getVersion()
            ),
            'error_message' => '',
        ];

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }

    /**
     * Log information about the last shown advertisement
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $this->disableAdminUsage();
        return $this->markUserNotified();
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
