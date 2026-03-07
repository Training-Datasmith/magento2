<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Store\Model\Store;

/**
 * @deprecated 100.1.0
 * @see we are not using it anymore
 */
class Baseurl implements \Magento\Framework\Notification\MessageInterface
{
    public function __construct(protected \Magento\Framework\App\Config\ScopeConfigInterface $_config, protected \Magento\Store\Model\StoreManagerInterface $_storeManager, protected \Magento\Framework\UrlInterface $_urlBuilder, protected \Magento\Framework\App\Config\ValueFactory $_configValueFactory)
    {
    }

    /**
     * Get url for config settings where base url option can be changed
     *
     * @return string
     */
    protected function _getConfigUrl()
    {
        $output = '';
        $defaultUnsecure = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        $defaultSecure = $this->_config->getValue(Store::XML_PATH_SECURE_BASE_URL, 'default');

        if ($defaultSecure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER ||
            $defaultUnsecure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
        ) {
            $output = $this->_urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'web']);
        } else {
            /** @var $dataCollection \Magento\Config\Model\ResourceModel\Config\Data\Collection */
            $dataCollection = $this->_configValueFactory->create()->getCollection();
            $dataCollection->addValueFilter(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER);

            /** @var $data \Magento\Framework\App\Config\ValueInterface */
            foreach ($dataCollection as $data) {
                if ($data->getScope() == 'stores') {
                    $code = $this->_storeManager->getStore($data->getScopeId())->getCode();
                    $output = $this->_urlBuilder->getUrl(
                        'adminhtml/system_config/edit',
                        ['section' => 'web', 'store' => $code]
                    );
                    break;
                } elseif ($data->getScope() == 'websites') {
                    $code = $this->_storeManager->getWebsite($data->getScopeId())->getCode();
                    $output = $this->_urlBuilder->getUrl(
                        'adminhtml/system_config/edit',
                        ['section' => 'web', 'website' => $code]
                    );
                    break;
                }
            }
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     */
    public function getIdentity(): string
    {
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('BASE_URL' . $this->_getConfigUrl());
    }

    /**
     * Check whether
     */
    public function isDisplayed(): bool
    {
        return (bool)$this->_getConfigUrl();
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            '{{base_url}} is not recommended to use in a production environment to declare the Base Unsecure '
            . 'URL / Base Secure URL. We highly recommend changing this value in your Magento '
            . '<a href="%1">configuration</a>.',
            $this->_getConfigUrl()
        );
    }

    /**
     * Retrieve message severity
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_CRITICAL;
    }
}
