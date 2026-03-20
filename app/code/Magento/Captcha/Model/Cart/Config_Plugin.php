<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Model\Cart;

class Config_Plugin
{
    /**
     * @var \Magento\Captcha\Model\Checkout\ConfigProvider
     */
    protected $config_provider;
    /**
     * @param \Magento\Captcha\Model\Checkout\ConfigProvider $configProvider
     */
    public function __construct(\Magento\Captcha\Model\Checkout\Config_Provider $config_provider)
    {
        $this->config_provider = $config_provider;
    }
    /**
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_get_config(\Magento\Checkout\Block\Cart\Sidebar $subject, array $result)
    {
        return array_merge_recursive($result, $this->config_provider->get_config());
    }
}