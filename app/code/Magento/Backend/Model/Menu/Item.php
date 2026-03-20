<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

use Magento\Backend\Model\Menu;
use Magento\Store\Model\Scope_Interface;
/**
 * Menu item. Should be used to create nested menu structures with \Magento\Backend\Model\Menu
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Item
{
    /**
     * Menu item id
     *
     * @var string
     */
    protected $_id;
    /**
     * Menu item title
     *
     * @var string
     */
    protected $_title;
    /**
     * Module of menu item
     *
     * @var string
     */
    protected $_module_name;
    /**
     * Menu item sort index in list
     *
     * @var string
     */
    protected $_sort_index = null;
    /**
     * Menu item action
     *
     * @var string
     */
    protected $_action = null;
    /**
     * Parent menu item id
     *
     * @var string
     */
    protected $_parent_id = null;
    /**
     * Acl resource of menu item
     *
     * @var string
     */
    protected $_resource;
    /**
     * Item tooltip text
     *
     * @var string
     */
    protected $_tooltip;
    /**
     * Path from root element in tree
     *
     * @var string
     */
    protected $_path = '';
    /**
     * Acl
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_acl;
    /**
     * Module that item is dependent on
     *
     * @var string|null
     */
    protected $_depends_on_module;
    /**
     * Global config option that item is dependent on
     *
     * @var string|null
     */
    protected $_depends_on_config;
    /**
     * Submenu item list
     *
     * @var Menu
     */
    protected $_submenu;
    /**
     * @var \Magento\Backend\Model\MenuFactory
     */
    protected $_menu_factory;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url_model;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scope_config;
    /**
     * @var \Magento\Backend\Model\Menu\Item\Validator
     */
    protected $_validator;
    /**
     * Serialized submenu string
     *
     * @var string
     * @deprecated 100.2.0
     */
    protected $_serialized_submenu;
    /**
     * Module list
     *
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_module_list;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $_module_manager;
    /**
     * Menu item target
     *
     * @var string|null
     */
    private $target;
    /**
     * @param Item\Validator $validator
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Model\MenuFactory $menuFactory
     * @param \Magento\Backend\Model\UrlInterface $urlModel
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(\Magento\Backend\Model\Menu\Item\Validator $validator, \Magento\Framework\Authorization_Interface $authorization, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Backend\Model\Menu_Factory $menu_factory, \Magento\Backend\Model\Url_Interface $url_model, \Magento\Framework\Module\Module_List_Interface $module_list, \Magento\Framework\Module\Manager $module_manager, array $data = [])
    {
        $this->_validator = $validator;
        $this->_validator->validate($data);
        $this->_module_manager = $module_manager;
        $this->_acl = $authorization;
        $this->_scope_config = $scope_config;
        $this->_menu_factory = $menu_factory;
        $this->_url_model = $url_model;
        $this->_module_list = $module_list;
        $this->populate_from_array($data);
    }
    /**
     * Retrieve argument element, or default value
     *
     * @param array $array
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _get_argument(array $array, $key, $default_value = null)
    {
        return isset($array[$key]) ? $array[$key] : $default_value;
    }
    /**
     * Retrieve item id
     *
     * @return string
     */
    public function get_id()
    {
        return $this->_id;
    }
    /**
     * Retrieve item target
     *
     * @return string|null
     * @since 100.2.0
     */
    public function get_target()
    {
        return $this->target;
    }
    /**
     * Check whether item has subnodes
     *
     * @return bool
     */
    public function has_children()
    {
        return null !== $this->_submenu && (bool) $this->_submenu->count();
    }
    /**
     * Retrieve submenu
     *
     * @return Menu
     */
    public function get_children()
    {
        if (!$this->_submenu) {
            $this->_submenu = $this->_menu_factory->create();
        }
        return $this->_submenu;
    }
    /**
     * Retrieve menu item url
     *
     * @return string
     */
    public function get_url()
    {
        if ((bool) $this->_action) {
            return $this->_url_model->get_url((string) $this->_action, ['_cache_secret_key' => true]);
        }
        return '#';
    }
    /**
     * Retrieve menu item action
     *
     * @return string
     */
    public function get_action()
    {
        return $this->_action;
    }
    /**
     * Set Item action
     *
     * @param string $action
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_action($action)
    {
        $this->_validator->validate_param('action', $action);
        $this->_action = $action;
        return $this;
    }
    /**
     * Check whether item has javascript callback on click
     *
     * @return bool
     */
    public function has_click_callback()
    {
        return $this->get_url() == '#';
    }
    /**
     * Retrieve item click callback
     *
     * @return string
     */
    public function get_click_callback()
    {
        if ($this->get_url() == '#') {
            return 'return false;';
        }
        return '';
    }
    /**
     * Retrieve tooltip text title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }
    /**
     * Set Item title
     *
     * @param string $title
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_title($title)
    {
        $this->_validator->validate_param('title', $title);
        $this->_title = $title;
        return $this;
    }
    /**
     * Check whether item has tooltip text
     *
     * @return bool
     */
    public function has_tooltip()
    {
        return (bool) $this->_tooltip;
    }
    /**
     * Retrieve item tooltip text
     *
     * @return string
     */
    public function get_tooltip()
    {
        return $this->_tooltip;
    }
    /**
     * Set Item tooltip
     *
     * @param string $tooltip
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_tooltip($tooltip)
    {
        $this->_validator->validate_param('toolTip', $tooltip);
        $this->_tooltip = $tooltip;
        return $this;
    }
    /**
     * Set Item module
     *
     * @param string $module
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_module($module)
    {
        $this->_validator->validate_param('module', $module);
        $this->_module_name = $module;
        return $this;
    }
    /**
     * Set Item module dependency
     *
     * @param string $moduleName
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_module_dependency($module_name)
    {
        $this->_validator->validate_param('dependsOnModule', $module_name);
        $this->_depends_on_module = $module_name;
        return $this;
    }
    /**
     * Set Item config dependency
     *
     * @param string $configPath
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_config_dependency($config_path)
    {
        $this->_validator->validate_param('dependsOnConfig', $config_path);
        $this->_depends_on_config = $config_path;
        return $this;
    }
    /**
     * Check whether item is disabled. Disabled items are not shown to user
     *
     * @return bool
     */
    public function is_disabled()
    {
        return !$this->_module_manager->is_output_enabled($this->_module_name) || !$this->_is_module_dependencies_available() || !$this->_is_config_dependencies_available();
    }
    /**
     * Check whether module that item depends on is active
     *
     * @return bool
     */
    protected function _is_module_dependencies_available()
    {
        if ($this->_depends_on_module) {
            $module = $this->_depends_on_module;
            return $this->_module_list->has($module);
        }
        return true;
    }
    /**
     * Check whether config dependency is available
     *
     * @return bool
     */
    protected function _is_config_dependencies_available()
    {
        if ($this->_depends_on_config) {
            return $this->_scope_config->is_set_flag((string) $this->_depends_on_config, Scope_Interface::SCOPE_STORE);
        }
        return true;
    }
    /**
     * Check whether item is allowed to the user
     *
     * @return bool
     */
    public function is_allowed()
    {
        try {
            return $this->_acl->is_allowed((string) $this->_resource);
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Get menu item data represented as an array
     *
     * @return array
     * @since 100.2.0
     */
    public function to_array()
    {
        return ['parent_id' => $this->_parent_id, 'module' => $this->_module_name, 'sort_index' => $this->_sort_index, 'dependsOnConfig' => $this->_depends_on_config, 'id' => $this->_id, 'resource' => $this->_resource, 'path' => $this->_path, 'action' => $this->_action, 'dependsOnModule' => $this->_depends_on_module, 'toolTip' => $this->_tooltip, 'title' => $this->_title, 'target' => $this->target, 'sub_menu' => isset($this->_submenu) ? $this->_submenu->to_array() : null];
    }
    /**
     * Populate the menu item with data from array
     *
     * @param array $data
     * @return void
     * @since 100.2.0
     */
    public function populate_from_array(array $data)
    {
        $this->_parent_id = $this->_get_argument($data, 'parent_id');
        $this->_module_name = $this->_get_argument($data, 'module', 'Magento_Backend');
        $this->_sort_index = $this->_get_argument($data, 'sort_index');
        $this->_depends_on_config = $this->_get_argument($data, 'dependsOnConfig');
        $this->_id = $this->_get_argument($data, 'id');
        $this->_resource = $this->_get_argument($data, 'resource');
        $this->_path = $this->_get_argument($data, 'path', '');
        $this->_action = $this->_get_argument($data, 'action');
        $this->_depends_on_module = $this->_get_argument($data, 'dependsOnModule');
        $this->_tooltip = $this->_get_argument($data, 'toolTip');
        $this->_title = $this->_get_argument($data, 'title');
        $this->target = $this->_get_argument($data, 'target');
        $this->_submenu = null;
        if (isset($data['sub_menu'])) {
            $menu = $this->_menu_factory->create();
            $menu->populate_from_array($data['sub_menu']);
            $this->_submenu = $menu;
        }
    }
}