<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Config
{
    public const CACHE_ID = 'backend_menu_config';
    public const CACHE_MENU_OBJECT = 'backend_menu_object';
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_config_cache_type;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Backend\Model\MenuFactory
     */
    protected $_menu_factory;
    /**
     * Menu model
     *
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader
     */
    protected $_config_reader;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scope_config;
    /**
     * @var \Magento\Backend\Model\Menu\AbstractDirector
     */
    protected $_director;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_app_state;
    /**
     * @var Builder
     */
    private $_menu_builder;
    /**
     * @param \Magento\Backend\Model\Menu\Builder $menuBuilder
     * @param \Magento\Backend\Model\Menu\AbstractDirector $menuDirector
     * @param \Magento\Backend\Model\MenuFactory $menuFactory
     * @param \Magento\Backend\Model\Menu\Config\Reader $configReader
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\Magento\Backend\Model\Menu\Builder $menu_builder, \Magento\Backend\Model\Menu\Abstract_Director $menu_director, \Magento\Backend\Model\Menu_Factory $menu_factory, \Magento\Backend\Model\Menu\Config\Reader $config_reader, \Magento\Framework\App\Cache\Type\Config $config_cache_type, \Magento\Framework\Event\Manager_Interface $event_manager, \Psr\Log\Logger_Interface $logger, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\App\State $app_state)
    {
        $this->_menu_builder = $menu_builder;
        $this->_director = $menu_director;
        $this->_config_cache_type = $config_cache_type;
        $this->_event_manager = $event_manager;
        $this->_logger = $logger;
        $this->_menu_factory = $menu_factory;
        $this->_config_reader = $config_reader;
        $this->_scope_config = $scope_config;
        $this->_app_state = $app_state;
    }
    /**
     * Build menu model from config
     *
     * @return \Magento\Backend\Model\Menu
     * @throws \Exception|\InvalidArgumentException
     * @throws \Exception
     * @throws \BadMethodCallException|\Exception
     * @throws \Exception|\OutOfRangeException
     */
    public function get_menu()
    {
        try {
            $this->_init_menu();
            return $this->_menu;
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\BadMethodCallException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\OutOfRangeException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Initialize menu object
     *
     * @return void
     */
    protected function _init_menu()
    {
        if (!$this->_menu) {
            $this->_menu = $this->_menu_factory->create();
            $cache = $this->_config_cache_type->load(self::CACHE_MENU_OBJECT);
            if ($cache) {
                $this->_menu->unserialize($cache);
                return;
            }
            $this->_director->direct($this->_config_reader->read($this->_app_state->get_area_code()), $this->_menu_builder, $this->_logger);
            $this->_menu = $this->_menu_builder->get_result($this->_menu);
            $this->_config_cache_type->save($this->_menu->serialize(), self::CACHE_MENU_OBJECT);
        }
    }
}