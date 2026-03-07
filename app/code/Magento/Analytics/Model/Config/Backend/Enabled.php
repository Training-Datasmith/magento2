<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
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
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        /**
         * Service for processing of activation/deactivation MBI subscription.
         */
        private readonly SubscriptionHandler $subscriptionHandler,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Add additional handling after config value was saved.
     *
     * @return Value
     * @throws LocalizedException
     */
    public function afterSave()
    {
        try {
            if ($this->isValueChanged()) {
                $enabled = $this->getData('value');
                $enabled ? $this->subscriptionHandler->processEnabled() : $this->subscriptionHandler->processDisabled();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            throw new LocalizedException(__('There was an error save new configuration value.'));
        }

        return parent::afterSave();
    }
}
