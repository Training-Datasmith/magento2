<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Plugin\Controller\System\Config;

use Magento\AsyncConfig\Api\AsyncConfigPublisherInterface;
use Magento\AsyncConfig\Setup\ConfigOptionsList;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Message\ManagerInterface;

class SaveAsyncConfigPlugin
{
    public function __construct(private readonly DeploymentConfig $deploymentConfig, private readonly AsyncConfigPublisherInterface $asyncConfigPublisher, private readonly RedirectFactory $resultRedirectFactory, private readonly ManagerInterface $messageManager)
    {
    }

    /**
     * Around Config save controller
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        if (!$this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_ASYNC_CONFIG_SAVE)) {
            return $proceed();
        }
        $configData = $subject->getConfigData();
        $this->asyncConfigPublisher->saveConfigData($configData);
        $this->messageManager->addSuccessMessage(__('Configuration changes will be applied by consumer soon.'));
        $subject->_saveState($subject->getRequest()->getPost('config_state'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true,
            ]
        );
    }
}
