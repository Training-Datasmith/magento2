<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager\Config_Loader_Interface;
/**
 * Application area model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class Area implements \Magento\Framework\App\Area_Interface
{
    public const AREA_GLOBAL = 'global';
    public const AREA_FRONTEND = 'frontend';
    public const AREA_ADMINHTML = 'adminhtml';
    public const AREA_DOC = 'doc';
    public const AREA_CRONTAB = 'crontab';
    public const AREA_WEBAPI_REST = 'webapi_rest';
    public const AREA_WEBAPI_SOAP = 'webapi_soap';
    public const AREA_GRAPHQL = 'graphql';
    /**
     * @deprecated
     */
    public const AREA_ADMIN = 'admin';
    /**
     * Area parameter.
     */
    public const PARAM_AREA = 'area';
    /**
     * Array of area loaded parts
     *
     * @var array
     */
    protected $_loaded_parts;
    /**
     * Area code
     *
     * @var string
     */
    protected $_code;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @var ConfigLoaderInterface
     */
    protected $_di_config_loader;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * Core design
     *
     * @var \Magento\Framework\App\DesignInterface
     */
    protected $_design;
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scope_resolver;
    /**
     * @var \Magento\Framework\View\DesignExceptions
     */
    protected $_design_exceptions;
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigLoaderInterface $diConfigLoader
     * @param \Magento\Framework\App\DesignInterface $design
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\View\DesignExceptions $designExceptions
     * @param string $areaCode
     */
    public function __construct(\Psr\Log\Logger_Interface $logger, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Translate_Interface $translator, \Magento\Framework\Object_Manager_Interface $object_manager, Config_Loader_Interface $di_config_loader, \Magento\Framework\App\Design_Interface $design, \Magento\Framework\App\Scope_Resolver_Interface $scope_resolver, \Magento\Framework\View\Design_Exceptions $design_exceptions, $area_code)
    {
        $this->_code = $area_code;
        $this->_object_manager = $object_manager;
        $this->_di_config_loader = $di_config_loader;
        $this->_event_manager = $event_manager;
        $this->_translator = $translator;
        $this->_logger = $logger;
        $this->_design = $design;
        $this->_scope_resolver = $scope_resolver;
        $this->_design_exceptions = $design_exceptions;
    }
    /**
     * Load area data
     *
     * @param   string|null $part
     * @return  $this
     */
    public function load($part = null)
    {
        if ($part === null) {
            $this->_load_part(self::PART_CONFIG)->_load_part(self::PART_DESIGN)->_load_part(self::PART_TRANSLATE);
        } else {
            $this->_load_part($part);
        }
        return $this;
    }
    /**
     * Detect and apply design for the area
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function detect_design($request = null)
    {
        if ($this->_code == self::AREA_FRONTEND) {
            $is_design_exception = $request && $this->_apply_user_agent_design_exception($request);
            if (!$is_design_exception) {
                $this->_design->load_change($this->_scope_resolver->get_scope()->get_id())->change_design($this->_get_design());
            }
        }
    }
    /**
     * Analyze user-agent information to override custom design settings
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _apply_user_agent_design_exception($request)
    {
        try {
            $theme = $this->_design_exceptions->get_theme_by_request($request);
            if (false !== $theme) {
                $this->_get_design()->set_design_theme($theme);
                return true;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return false;
    }
    /**
     * Get Design instance
     *
     * @return \Magento\Framework\View\DesignInterface
     */
    protected function _get_design()
    {
        return $this->_object_manager->get(\Magento\Framework\View\Design_Interface::class);
    }
    /**
     * Loading part of area
     *
     * @param   string $part
     * @return  $this
     */
    protected function _load_part($part)
    {
        if (isset($this->_loaded_parts[$part])) {
            return $this;
        }
        \Magento\Framework\Profiler::start('load_area:' . $this->_code . '.' . $part, ['group' => 'load_area', 'area_code' => $this->_code, 'part' => $part]);
        switch ($part) {
            case self::PART_CONFIG:
                $this->_init_config();
                break;
            case self::PART_TRANSLATE:
                $this->_init_translate();
                break;
            case self::PART_DESIGN:
                $this->_init_design();
                break;
        }
        $this->_loaded_parts[$part] = true;
        \Magento\Framework\Profiler::stop('load_area:' . $this->_code . '.' . $part);
        return $this;
    }
    /**
     * Load area configuration
     *
     * @return $this
     */
    protected function _init_config()
    {
        $this->_object_manager->configure($this->_di_config_loader->load($this->_code));
        return $this;
    }
    /**
     * Initialize translate object.
     *
     * @return $this
     */
    protected function _init_translate()
    {
        $this->_translator->load_data($this->_code, false);
        \Magento\Framework\Phrase::set_renderer($this->_object_manager->get(\Magento\Framework\Phrase\Renderer_Interface::class));
        return $this;
    }
    /**
     * Initialize design
     *
     * @return $this
     */
    protected function _init_design()
    {
        $this->_get_design()->set_area($this->_code)->set_default_design_theme();
        return $this;
    }
}