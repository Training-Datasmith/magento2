<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Controller\Adminhtml\BIEssentials;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provides link to BI Essentials signup
 */
class SignUp extends Action implements HttpGetActionInterface
{
    /**
     * Path to config value with URL to BI Essentials sign-up page.
     */
    private string $urlBIEssentialsConfigPath = 'analytics/url/bi_essentials';

    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magento_Analytics::bi_essentials';

    public function __construct(
        Context $context,
        private readonly ScopeConfigInterface $config
    ) {
        parent::__construct($context);
    }

    /**
     * Provides link to BI Essentials signup
     *
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setUrl(
            $this->config->getValue($this->urlBIEssentialsConfigPath)
        );
    }
}
