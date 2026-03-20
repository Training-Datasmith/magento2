<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Helper;

/**
 * Bundle helper
 */
class Data extends \Magento\Framework\App\Helper\Abstract_Helper
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $config;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Model\Product_Types\Config_Interface $config)
    {
        $this->config = $config;
        parent::__construct($context);
    }
    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     */
    public function get_allowed_selection_types()
    {
        $config_data = $this->config->get_type(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        return $config_data['allowed_selection_types'] ?? [];
    }
}