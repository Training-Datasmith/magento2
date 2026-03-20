<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Controller\Adminhtml\Bi_Essentials;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * Provides link to BI Essentials signup
 */
class Sign_Up extends Action implements Http_Get_Action_Interface
{
    /**
     * Path to config value with URL to BI Essentials sign-up page.
     */
    private string $url_bi_essentials_config_path = 'analytics/url/bi_essentials';
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magento_Analytics::bi_essentials';
    public function __construct(Context $context, private readonly Scope_Config_Interface $config)
    {
        parent::__construct($context);
    }
    /**
     * Provides link to BI Essentials signup
     *
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        return $this->result_redirect_factory->create()->set_url($this->config->get_value($this->url_bi_essentials_config_path));
    }
}