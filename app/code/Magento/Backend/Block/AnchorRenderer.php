<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Framework\Escaper;
/**
 * Class AnchorRenderer
 */
class Anchor_Renderer
{
    /**
     * @var MenuItemChecker
     */
    private $menu_item_checker;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @param MenuItemChecker $menuItemChecker
     * @param Escaper $escaper
     */
    public function __construct(Menu_Item_Checker $menu_item_checker, Escaper $escaper)
    {
        $this->menu_item_checker = $menu_item_checker;
        $this->escaper = $escaper;
    }
    /**
     * Render menu item anchor.
     *
     *  It is used in backend menu to render anchor menu.
     *
     * @param Item|false $activeItem Can be false if menu item is inaccessible
     * but was triggered directly using controller. It is a legacy code behaviour.
     * @param Item $menuItem
     * @param int $level
     * @return string
     */
    public function render_anchor($active_item, Item $menu_item, $level)
    {
        if ($level == 1 && $menu_item->get_url() == '#') {
            $output = '';
            if ($menu_item->has_children()) {
                $output = '<strong class="submenu-group-title" role="presentation">' . '<span>' . $this->escaper->escape_html(__($menu_item->get_title())) . '</span>' . '</strong>';
            }
        } else {
            $target = $menu_item->get_target() ? 'target=' . $menu_item->get_target() : '';
            $output = '<a href="' . $menu_item->get_url() . '" ' . $target . ' ' . $this->_render_item_anchor_title($menu_item) . $this->_render_item_onclick_function($menu_item) . ' class="' . ($this->menu_item_checker->is_item_active($active_item, $menu_item, $level) ? '_active' : '') . '">' . '<span>' . $this->escaper->escape_html(__($menu_item->get_title())) . '</span>' . '</a>';
        }
        return $output;
    }
    /**
     * Render menu item anchor title
     *
     * @param Item $menuItem
     * @return string
     */
    private function _render_item_anchor_title($menu_item)
    {
        return $menu_item->has_tooltip() ? 'title="' . __($menu_item->get_tooltip()) . '"' : '';
    }
    /**
     * Render menu item onclick function
     *
     * @param Item $menuItem
     * @return string
     */
    private function _render_item_onclick_function($menu_item)
    {
        return $menu_item->has_click_callback() ? ' onclick="' . $menu_item->get_click_callback() . '"' : '';
    }
}