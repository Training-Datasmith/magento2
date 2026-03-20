<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 100.0.2
 */
class Cache extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'cache';
        $this->_header_text = __('Cache Storage Management');
        parent::_construct();
        $this->button_list->remove('add');
        if ($this->_authorization->is_allowed('Magento_Backend::flush_magento_cache')) {
            $this->button_list->add('flush_magento', ['label' => __('Flush Magento Cache'), 'title' => __('Removes only Magento-generated cache. Safe to use when refreshing outdated data.'), 'onclick' => 'setLocation(\'' . $this->get_flush_system_url() . '\')', 'class' => 'primary flush-cache-magento']);
        }
        if ($this->_authorization->is_allowed('Magento_Backend::flush_cache_storage')) {
            $message = $this->escape_js($this->escape_html(__('The cache storage may contain additional data. Are you sure that you want to flush it?')));
            $this->button_list->add('flush_system', ['label' => __('Flush Cache Storage'), 'title' => __('Clears all cache data, including shared or external cache. ' . 'Use if standard cache refresh does not resolve issues.'), 'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $this->get_flush_storage_url() . '\')', 'class' => 'flush-cache-storage']);
        }
    }
    /**
     * Get url for clean cache storage
     *
     * @return string
     */
    public function get_flush_storage_url()
    {
        return $this->get_url('adminhtml/*/flushAll');
    }
    /**
     * Get url for clean cache storage
     *
     * @return string
     */
    public function get_flush_system_url()
    {
        return $this->get_url('adminhtml/*/flushSystem');
    }
}