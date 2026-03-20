<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Framework\App\Cache\Type_List_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
/**
 * Config value backend model.
 */
class Enabled extends Value
{
    /**
     * Path to field subscription enabled into config structure.
     */
    public const XML_ENABLED_CONFIG_STRUCTURE_PATH = 'analytics/general/enabled';
    public function __construct(
        Context $context,
        Registry $registry,
        Scope_Config_Interface $config,
        Type_List_Interface $cache_type_list,
        /**
         * Service for processing of activation/deactivation MBI subscription.
         */
        private readonly Subscription_Handler $subscription_handler,
        ?Abstract_Resource $resource = null,
        ?Abstract_Db $resource_collection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $config, $cache_type_list, $resource, $resource_collection, $data);
    }
    /**
     * Add additional handling after config value was saved.
     *
     * @return Value
     * @throws LocalizedException
     */
    public function after_save()
    {
        try {
            if ($this->is_value_changed()) {
                $enabled = $this->get_data('value');
                $enabled ? $this->subscription_handler->process_enabled() : $this->subscription_handler->process_disabled();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->get_message());
            throw new Localized_Exception(__('There was an error save new configuration value.'));
        }
        return parent::after_save();
    }
}