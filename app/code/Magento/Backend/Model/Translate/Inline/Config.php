<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Translate\Inline;

/**
 * Backend Inline Translation config
 * @api
 * @since 100.0.2
 */
class Config implements \Magento\Framework\Translate\Inline\Config_Interface
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;
    /**
     * @var \Magento\Developer\Helper\Data
     */
    protected $dev_helper;
    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Developer\Helper\Data $devHelper
     */
    public function __construct(\Magento\Backend\App\Config_Interface $config, \Magento\Developer\Helper\Data $dev_helper)
    {
        $this->config = $config;
        $this->dev_helper = $dev_helper;
    }
    /**
     * {@inheritdoc}
     */
    public function is_active($scope = null)
    {
        return $this->config->is_set_flag('dev/translate_inline/active_admin');
    }
    /**
     * {@inheritdoc}
     */
    public function is_dev_allowed($scope = null)
    {
        return $this->dev_helper->is_dev_allowed($scope);
    }
}