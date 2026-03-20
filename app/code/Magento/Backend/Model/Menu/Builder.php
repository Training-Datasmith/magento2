<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

/**
 * Menu builder object. Retrieves commands (\Magento\Backend\Model\Menu\Builder\AbstractCommand)
 * to build menu (\Magento\Backend\Model\Menu)
 * @api
 * @since 100.0.2
 */
class Builder
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand[]
     */
    protected $_commands = [];
    /**
     * @var \Magento\Backend\Model\Menu\Item\Factory
     */
    protected $_item_factory;
    /**
     * @param \Magento\Backend\Model\Menu\Item\Factory $menuItemFactory
     */
    public function __construct(\Magento\Backend\Model\Menu\Item\Factory $menu_item_factory)
    {
        $this->_item_factory = $menu_item_factory;
    }
    /**
     * Process provided command object
     *
     * @param \Magento\Backend\Model\Menu\Builder\AbstractCommand $command
     * @return $this
     */
    public function process_command(\Magento\Backend\Model\Menu\Builder\Abstract_Command $command)
    {
        if (!isset($this->_commands[$command->get_id()])) {
            $this->_commands[$command->get_id()] = $command;
        } else {
            $this->_commands[$command->get_id()]->chain($command);
        }
        return $this;
    }
    /**
     * Populate menu object
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu
     * @throws \OutOfRangeException in case given parent id does not exists
     */
    public function get_result(\Magento\Backend\Model\Menu $menu)
    {
        /** @var $items \Magento\Backend\Model\Menu\Item[] */
        $params = [];
        $items = [];
        // Create menu items
        foreach ($this->_commands as $id => $command) {
            $params[$id] = $command->execute();
            $item = $this->_item_factory->create($params[$id]);
            $items[$id] = $item;
        }
        // Build menu tree based on "parent" param
        foreach ($items as $id => $item) {
            $sort_order = $this->_get_param($params[$id], 'sortOrder');
            $parent_id = $this->_get_param($params[$id], 'parent');
            $is_removed = isset($params[$id]['removed']);
            if ($is_removed) {
                continue;
            }
            if (!$parent_id) {
                $menu->add($item, null, $sort_order);
            } else {
                if (!isset($items[$parent_id])) {
                    throw new \OutOfRangeException(sprintf('Specified invalid parent id (%s)', $parent_id));
                }
                if (isset($params[$parent_id]['removed'])) {
                    continue;
                }
                $items[$parent_id]->get_children()->add($item, null, $sort_order);
            }
        }
        return $menu;
    }
    /**
     * Retrieve param by name or default value
     *
     * @param array $params
     * @param string $paramName
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _get_param($params, $param_name, $default_value = null)
    {
        return $params[$param_name] ?? $default_value;
    }
}