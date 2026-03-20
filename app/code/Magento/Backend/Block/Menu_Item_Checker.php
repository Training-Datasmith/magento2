<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

use Magento\Backend\Model\Menu\Item;
/**
 * Class MenuItemChecker
 */
class Menu_Item_Checker
{
    /**
     * Check whether given menu item is currently selected.
     *
     * It is used in backend menu to highlight active menu item.
     *
     * @param Item|false $activeItem Can be false if menu item is inaccessible
     * but was triggered directly using controller. It is a legacy code behaviour.
     * @param Item $item
     * @param int $level
     * @return bool
     */
    public function is_item_active($active_item, Item $item, $level)
    {
        $output = false;
        if ($level == 0 && $active_item instanceof \Magento\Backend\Model\Menu\Item && $this->is_active_item_equal_or_child($active_item, $item)) {
            $output = true;
        }
        return $output;
    }
    /**
     * @param Item $activeItem,
     * @param Item $item
     * @return bool
     */
    private function is_active_item_equal_or_child($active_item, $item)
    {
        return $active_item->get_id() == $item->get_id() || $item->get_children()->get($active_item->get_id()) !== null;
    }
}