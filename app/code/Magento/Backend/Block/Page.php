<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Adminhtml page
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 100.0.2
 */
class Page extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale_resolver;
    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Locale\Resolver_Interface $locale_resolver, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_locale_resolver = $locale_resolver;
    }
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->page_config->add_body_class($this->_request->get_full_action_name('-'));
    }
    /**
     * Get current language
     *
     * @return string
     */
    public function get_lang()
    {
        if (!$this->has_data('lang')) {
            $this->set_data('lang', substr($this->_locale_resolver->get_locale(), 0, 2));
        }
        return $this->get_data('lang');
    }
    /**
     * Returns true if we are running in single store mode
     *
     * @return bool
     */
    public function is_single_store_mode()
    {
        return $this->_store_manager->is_single_store_mode();
    }
}