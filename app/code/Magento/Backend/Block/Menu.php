<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

/**
 * Backend menu block
 *
 * @method $this setAdditionalCacheKeyInfo(array $cacheKeyInfo)
 * @method array getAdditionalCacheKeyInfo()
 * @api
 * @since 100.0.2
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Menu extends \Magento\Backend\Block\Template
{
    public const CACHE_TAGS = 'BACKEND_MAINMENU';
    /**
     * @var string
     */
    protected $_container_renderer;
    /**
     * @var string
     */
    protected $_item_renderer;
    /**
     * Backend URL instance
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;
    /**
     * Current selected item
     *
     * @var \Magento\Backend\Model\Menu\Item|false|null
     */
    protected $_active_item_model = null;
    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    protected $_iterator_factory;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menu_config;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale_resolver;
    /**
     * @var MenuItemChecker
     */
    private $menu_item_checker;
    /**
     * @var AnchorRenderer
     */
    private $anchor_renderer;
    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    private $route_config;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @param MenuItemChecker|null $menuItemChecker
     * @param AnchorRenderer|null $anchorRenderer
     * @param \Magento\Framework\App\Route\ConfigInterface|null $routeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Model\Url_Interface $url, \Magento\Backend\Model\Menu\Filter\Iterator_Factory $iterator_factory, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Backend\Model\Menu\Config $menu_config, \Magento\Framework\Locale\Resolver_Interface $locale_resolver, array $data = [], ?Menu_Item_Checker $menu_item_checker = null, ?Anchor_Renderer $anchor_renderer = null, ?\Magento\Framework\App\Route\Config_Interface $route_config = null)
    {
        $this->_url = $url;
        $this->_iterator_factory = $iterator_factory;
        $this->_auth_session = $auth_session;
        $this->_menu_config = $menu_config;
        $this->_locale_resolver = $locale_resolver;
        $this->menu_item_checker = $menu_item_checker;
        $this->anchor_renderer = $anchor_renderer;
        $this->route_config = $route_config ?: \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\App\Route\Config_Interface::class);
        parent::__construct($context, $data);
    }
    /**
     * Initialize template and cache settings
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_cache_tags([self::CACHE_TAGS]);
    }
    /**
     * Render menu item anchor label
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @return string
     */
    protected function _get_anchor_label($menu_item)
    {
        return $this->escape_html(__($menu_item->get_title()));
    }
    /**
     * Render menu item mouse events
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @return string
     */
    protected function _render_mouse_event($menu_item)
    {
        return $menu_item->has_children() ? 'onmouseover="Element.addClassName(this,\'over\')" onmouseout="Element.removeClassName(this,\'over\')"' : '';
    }
    /**
     * Render item css class
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @param int $level
     * @return string
     */
    protected function _render_item_css_class($menu_item, $level)
    {
        $is_last = 0 == $level && (bool) $this->get_menu_model()->is_last($menu_item) ? 'last' : '';
        $is_item_active = $this->menu_item_checker->is_item_active($this->get_active_item_model(), $menu_item, $level) ? '_current _active' : '';
        $output = $is_item_active . ' ' . ($menu_item->has_children() ? 'parent' : '') . ' ' . $is_last . ' ' . 'level-' . $level;
        return $output;
    }
    /**
     * Get menu filter iterator
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu\Filter\Iterator
     */
    protected function _get_menu_iterator($menu)
    {
        return $this->_iterator_factory->create(['iterator' => $menu->getIterator()]);
    }
    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _after_to_html($html)
    {
        $html = preg_replace_callback('#' . \Magento\Backend\Model\Url_Interface::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\/].*)/([^\$].*)\$#U', [$this, '_callbackSecretKey'], $html);
        return $html;
    }
    /**
     * Replace Callback Secret Key
     *
     * @param string[] $match
     * @return string
     */
    protected function _callback_secret_key($match)
    {
        $route_id = $this->route_config->get_route_by_front_name($match[1]);
        return \Magento\Backend\Model\Url_Interface::SECRET_KEY_PARAM_NAME . '/' . $this->_url->get_secret_key($route_id ?: $match[1], $match[2], $match[3]);
    }
    /**
     * Retrieve cache lifetime
     *
     * @return int
     */
    public function get_cache_lifetime()
    {
        return 86400;
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function get_cache_key_info()
    {
        $cache_key_info = ['admin_top_nav', $this->get_active(), $this->_auth_session->get_user()->get_id(), $this->_locale_resolver->get_locale()];
        // Add additional key parameters if needed
        $new_cache_key_info = $this->get_additional_cache_key_info();
        if (is_array($new_cache_key_info) && !empty($new_cache_key_info)) {
            $cache_key_info = array_merge($cache_key_info, $new_cache_key_info);
        }
        return $cache_key_info;
    }
    /**
     * Get menu config model
     *
     * @return \Magento\Backend\Model\Menu
     */
    public function get_menu_model()
    {
        return $this->_menu_config->get_menu();
    }
    /**
     * Render menu
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @return string HTML
     */
    public function render_menu($menu, $level = 0)
    {
        $output = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : '') . ' >';
        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
        foreach ($this->_get_menu_iterator($menu) as $menu_item) {
            $output .= '<li ' . $this->_render_mouse_event($menu_item) . ' class="' . $this->_render_item_css_class($menu_item, $level) . '"' . $this->get_ui_id($menu_item->get_id()) . 'role="menuitem">';
            $output .= $this->anchor_renderer->render_anchor($this->get_active_item_model(), $menu_item, $level);
            if ($menu_item->has_children()) {
                $output .= $this->render_menu($menu_item->get_children(), $level + 1);
            }
            $output .= '</li>';
        }
        $output .= '</ul>';
        return $output;
    }
    /**
     * Count All Subnavigation Items
     *
     * @param \Magento\Backend\Model\Menu $items
     * @return int
     */
    protected function _count_items($items)
    {
        $total = count($items);
        foreach ($items as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->has_children()) {
                $total += $this->_count_items($item->get_children());
            }
        }
        return $total;
    }
    /**
     * Building Array with Column Brake Stops
     *
     * @param \Magento\Backend\Model\Menu $items
     * @param int $limit
     * @return array|void
     * @todo: Add Depth Level limit, and better logic for columns
     */
    protected function _column_brake($items, $limit)
    {
        $total = $this->_count_items($items);
        if ($total <= $limit) {
            return;
        }
        $result[] = ['total' => $total, 'max' => ceil($total / ceil($total / $limit))];
        $count = 0;
        foreach ($items as $item) {
            $place = $this->_count_items($item->get_children()) + 1;
            $count += $place;
            if ($place - $result[0]['max'] > $limit - $result[0]['max']) {
                $colbrake = true;
                $count = 0;
            } elseif ($count - $result[0]['max'] > $limit - $result[0]['max']) {
                $colbrake = true;
                $count = $place;
            } else {
                $colbrake = false;
            }
            $result[] = ['place' => $place, 'colbrake' => $colbrake];
        }
        if (isset($result[1]) && $result[1]['colbrake'] === true && isset($result[2])) {
            $result[2]['colbrake'] = true;
        }
        return $result;
    }
    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @param int $level
     * @param int $limit
     * @param int|null $id
     * @return string HTML code
     */
    protected function _add_sub_menu($menu_item, $level, $limit, $id = null)
    {
        $output = '';
        if (!$menu_item->has_children()) {
            return $output;
        }
        $output .= '<div class="submenu"' . ($level == 0 && isset($id) ? ' aria-labelledby="' . $id . '"' : '') . '>';
        $col_stops = [];
        if ($level == 0 && $limit) {
            $col_stops = $this->_column_brake($menu_item->get_children(), $limit);
            $output .= '<strong class="submenu-title">' . $this->_get_anchor_label($menu_item) . '</strong>';
            $output .= '<a href="#" class="action-close _close" data-role="close-submenu"></a>';
        }
        $output .= $this->render_navigation($menu_item->get_children(), $level + 1, $limit, $col_stops);
        $output .= '</div>';
        return $output;
    }
    /**
     * Render Navigation
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @param int $limit
     * @param array $colBrakes
     * @return string HTML
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function render_navigation($menu, $level = 0, $limit = 0, $col_brakes = [])
    {
        $item_position = 1;
        $output_start = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : 'role="menu"') . ' >';
        $output = '';
        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
        foreach ($this->_get_menu_iterator($menu) as $menu_item) {
            $menu_id = $menu_item->get_id();
            $item_name = substr($menu_id, strrpos($menu_id, '::') + 2);
            $item_class = str_replace('_', '-', strtolower($item_name));
            if (is_array($col_brakes) && count($col_brakes) && $col_brakes[$item_position]['colbrake'] && $item_position != 1) {
                $output .= '</ul></li><li class="column"><ul role="menu">';
            }
            $id = $this->get_js_id($menu_item->get_id());
            $sub_menu = $this->_add_sub_menu($menu_item, $level, $limit, $id);
            $anchor = $this->anchor_renderer->render_anchor($this->get_active_item_model(), $menu_item, $level);
            $output .= '<li ' . $this->get_ui_id($menu_item->get_id()) . ' class="item-' . $item_class . ' ' . $this->_render_item_css_class($menu_item, $level) . ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '') . '" role="menu-item">' . $anchor . $sub_menu . '</li>';
            $item_position++;
        }
        if (is_array($col_brakes) && count($col_brakes) && $limit) {
            $output = '<li class="column"><ul role="menu">' . $output . '</ul></li>';
        }
        return $output_start . $output . '</ul>';
    }
    /**
     * Get current selected menu item
     *
     * @return \Magento\Backend\Model\Menu\Item|false
     */
    public function get_active_item_model()
    {
        if ($this->_active_item_model === null) {
            $this->_active_item_model = $this->get_menu_model()->get($this->get_active());
            if (false == $this->_active_item_model instanceof \Magento\Backend\Model\Menu\Item) {
                $this->_active_item_model = false;
            }
        }
        return $this->_active_item_model;
    }
}