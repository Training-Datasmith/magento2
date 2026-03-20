<?php

declare (strict_types=1);
/**
 * Abstract helper context
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Helper;

/**
 * Constructor modification point for Magento\Framework\App\Helper.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 */
class Context implements \Magento\Framework\Object_Manager\Context_Interface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_module_manager;
    /**
     * @var  \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_http_request;
    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     */
    protected $_cache_config;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url_builder;
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_http_header;
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
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\Cache\ConfigInterface $cacheConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\Url\Encoder_Interface $url_encoder, \Magento\Framework\Url\Decoder_Interface $url_decoder, \Psr\Log\Logger_Interface $logger, \Magento\Framework\Module\Manager $module_manager, \Magento\Framework\App\Request_Interface $http_request, \Magento\Framework\Cache\Config_Interface $cache_config, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Url_Interface $url_builder, \Magento\Framework\HTTP\Header $http_header, \Magento\Framework\HTTP\Php_Environment\Remote_Address $remote_address, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config)
    {
        $this->_module_manager = $module_manager;
        $this->_http_request = $http_request;
        $this->_cache_config = $cache_config;
        $this->_event_manager = $event_manager;
        $this->_logger = $logger;
        $this->_url_builder = $url_builder;
        $this->_http_header = $http_header;
        $this->_remote_address = $remote_address;
        $this->url_encoder = $url_encoder;
        $this->url_decoder = $url_decoder;
        $this->scope_config = $scope_config;
    }
    /**
     * Get module manager.
     *
     * @return \Magento\Framework\Module\Manager
     */
    public function get_module_manager()
    {
        return $this->_module_manager;
    }
    /**
     * Get url builder.
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function get_url_builder()
    {
        return $this->_url_builder;
    }
    /**
     * Get request.
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function get_request()
    {
        return $this->_http_request;
    }
    /**
     * Get cache configs.
     *
     * @return \Magento\Framework\Cache\ConfigInterface
     */
    public function get_cache_config()
    {
        return $this->_cache_config;
    }
    /**
     * Get event manager.
     *
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function get_event_manager()
    {
        return $this->_event_manager;
    }
    /**
     * Get logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function get_logger()
    {
        return $this->_logger;
    }
    /**
     * Get http header.
     *
     * @return \Magento\Framework\HTTP\Header
     */
    public function get_http_header()
    {
        return $this->_http_header;
    }
    /**
     * Get remote address.
     *
     * @return \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public function get_remote_address()
    {
        return $this->_remote_address;
    }
    /**
     * Get url encoder.
     *
     * @return \Magento\Framework\Url\EncoderInterface
     */
    public function get_url_encoder()
    {
        return $this->url_encoder;
    }
    /**
     * Get url decoder.
     *
     * @return \Magento\Framework\Url\DecoderInterface
     */
    public function get_url_decoder()
    {
        return $this->url_decoder;
    }
    /**
     * Get scope config.
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function get_scope_config()
    {
        return $this->scope_config;
    }
}