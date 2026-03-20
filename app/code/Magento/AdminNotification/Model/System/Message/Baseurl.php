<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message;

use Magento\Store\Model\Store;
/**
 * @deprecated 100.1.0
 * @see we are not using it anymore
 */
class Baseurl implements \Magento\Framework\Notification\Message_Interface
{
    public function __construct(protected \Magento\Framework\App\Config\Scope_Config_Interface $_config, protected \Magento\Store\Model\Store_Manager_Interface $_store_manager, protected \Magento\Framework\Url_Interface $_url_builder, protected \Magento\Framework\App\Config\Value_Factory $_config_value_factory)
    {
    }
    /**
     * Get url for config settings where base url option can be changed
     *
     * @return string
     */
    protected function _get_config_url()
    {
        $output = '';
        $default_unsecure = $this->_config->get_value(Store::XML_PATH_UNSECURE_BASE_URL, 'default');
        $default_secure = $this->_config->get_value(Store::XML_PATH_SECURE_BASE_URL, 'default');
        if ($default_secure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER || $default_unsecure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER) {
            $output = $this->_url_builder->get_url('adminhtml/system_config/edit', ['section' => 'web']);
        } else {
            /** @var $dataCollection \Magento\Config\Model\ResourceModel\Config\Data\Collection */
            $data_collection = $this->_config_value_factory->create()->get_collection();
            $data_collection->add_value_filter(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER);
            /** @var $data \Magento\Framework\App\Config\ValueInterface */
            foreach ($data_collection as $data) {
                if ($data->get_scope() == 'stores') {
                    $code = $this->_store_manager->get_store($data->get_scope_id())->get_code();
                    $output = $this->_url_builder->get_url('adminhtml/system_config/edit', ['section' => 'web', 'store' => $code]);
                    break;
                } elseif ($data->get_scope() == 'websites') {
                    $code = $this->_store_manager->get_website($data->get_scope_id())->get_code();
                    $output = $this->_url_builder->get_url('adminhtml/system_config/edit', ['section' => 'web', 'website' => $code]);
                    break;
                }
            }
        }
        return $output;
    }
    /**
     * Retrieve unique message identity
     */
    public function get_identity(): string
    {
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('BASE_URL' . $this->_get_config_url());
    }
    /**
     * Check whether
     */
    public function is_displayed(): bool
    {
        return (bool) $this->_get_config_url();
    }
    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_text()
    {
        return __('{{base_url}} is not recommended to use in a production environment to declare the Base Unsecure ' . 'URL / Base Secure URL. We highly recommend changing this value in your Magento ' . '<a href="%1">configuration</a>.', $this->_get_config_url());
    }
    /**
     * Retrieve message severity
     */
    public function get_severity(): int
    {
        return self::SEVERITY_CRITICAL;
    }
}