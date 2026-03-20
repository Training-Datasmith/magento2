<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Helper;

/**
 * Abstract helper
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Abstract_Helper
{
    /**
     * Helper module name
     *
     * @var string
     */
    protected $_module_name;
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_module_manager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url_builder;
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_http_header;
    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remote_address;
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $url_encoder;
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $url_decoder;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scope_config;
    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     */
    protected $_cache_config;
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->_module_manager = $context->get_module_manager();
        $this->_logger = $context->get_logger();
        $this->_request = $context->get_request();
        $this->_url_builder = $context->get_url_builder();
        $this->_http_header = $context->get_http_header();
        $this->_event_manager = $context->get_event_manager();
        $this->_remote_address = $context->get_remote_address();
        $this->_cache_config = $context->get_cache_config();
        $this->url_encoder = $context->get_url_encoder();
        $this->url_decoder = $context->get_url_decoder();
        $this->scope_config = $context->get_scope_config();
    }
    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _get_request()
    {
        return $this->_request;
    }
    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _get_module_name()
    {
        if (!$this->_module_name) {
            $class = get_class($this);
            $this->_module_name = substr($class, 0, strpos($class, '\Helper'));
        }
        return str_replace('\\', '_', $this->_module_name);
    }
    /**
     * Check whether or not the module output is enabled in Configuration
     *
     * @param string $moduleName Full module name
     * @return boolean
     * use \Magento\Framework\Module\Manager::isOutputEnabled()
     */
    public function is_module_output_enabled($module_name = null)
    {
        if ($module_name === null) {
            $module_name = $this->_get_module_name();
        }
        return $this->_module_manager->is_output_enabled($module_name);
    }
    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _get_url($route, $params = [])
    {
        return $this->_url_builder->get_url($route, $params);
    }
}