<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message;

/**
 * @api
 * @since 100.0.2
 */
class Cache_Outdated implements \Magento\Framework\Notification\Message_Interface
{
    public function __construct(protected \Magento\Framework\Authorization_Interface $_authorization, protected \Magento\Framework\Url_Interface $_url_builder, protected \Magento\Framework\App\Cache\Type_List_Interface $_cache_type_list)
    {
    }
    /**
     * Get array of cache types which require data refresh
     */
    protected function _get_cache_types_for_refresh(): array
    {
        $output = [];
        foreach ($this->_cache_type_list->get_invalidated() as $type) {
            $output[] = $type->get_cache_type();
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
        return md5('cache' . implode(':', $this->_get_cache_types_for_refresh()));
    }
    /**
     * Check whether
     */
    public function is_displayed(): bool
    {
        return $this->_authorization->is_allowed('Magento_Backend::cache') && count($this->_get_cache_types_for_refresh()) > 0;
    }
    /**
     * Retrieve message text
     */
    public function get_text(): string
    {
        $cache_types = implode(', ', $this->_get_cache_types_for_refresh());
        $message = __('One or more of the Cache Types are invalidated: %1. ', $cache_types) . ' ';
        $url = $this->_url_builder->get_url('adminhtml/cache');
        return $message . __('Please go to <a href="%1">Cache Management</a> and refresh cache types.', $url);
    }
    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function get_link()
    {
        return $this->_url_builder->get_url('adminhtml/cache');
    }
    /**
     * Retrieve message severity
     */
    public function get_severity(): int
    {
        return \Magento\Framework\Notification\Message_Interface::SEVERITY_CRITICAL;
    }
}