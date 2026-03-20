<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer_Interface;
use Psr\Log\Logger_Interface;
/**
 * Backend menu model
 *
 * @api
 * @since 100.0.2
 */
class Menu extends \ArrayObject
{
    /**
     * Path in tree structure
     *
     * @var string
     */
    protected $_path = '';
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var Factory
     */
    private $menu_item_factory;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * Menu constructor
     *
     * @param LoggerInterface $logger
     * @param string $pathInMenuStructure
     * @param Factory|null $menuItemFactory
     * @param SerializerInterface|null $serializer
     */
    public function __construct(Logger_Interface $logger, $path_in_menu_structure = '', ?Factory $menu_item_factory = null, ?Serializer_Interface $serializer = null)
    {
        if ($path_in_menu_structure) {
            $this->_path = $path_in_menu_structure . '/';
        }
        $this->_logger = $logger;
        $this->set_iterator_class(\Magento\Backend\Model\Menu\Iterator::class);
        $this->menu_item_factory = $menu_item_factory ?: Object_Manager::get_instance()->create(Factory::class);
        $this->serializer = $serializer ?: Object_Manager::get_instance()->create(Serializer_Interface::class);
    }
    /**
     * Add child to menu item
     *
     * @param Item $item
     * @param string $parentId
     * @param int $index
     * @return void
     * @throws \InvalidArgumentException
     */
    public function add(Item $item, $parent_id = null, $index = null)
    {
        if ($parent_id !== null) {
            $parent_item = $this->get($parent_id);
            if ($parent_item === null) {
                throw new \InvalidArgumentException("Item with identifier {$parent_id} does not exist");
            }
            $parent_item->get_children()->add($item, null, $index);
        } else {
            $index = (int) $index;
            if (!isset($this[$index])) {
                $this->offsetSet($index, $item);
                $this->_logger->debug(sprintf('Add of item with id %s was processed', $item->get_id()));
            } else {
                $this->add($item, $parent_id, $index + 1);
            }
        }
    }
    /**
     * Retrieve menu item by id
     *
     * @param string $itemId
     * @return Item|null
     */
    public function get($item_id)
    {
        $result = null;
        /** @var Item $item */
        foreach ($this as $item) {
            if ($item->get_id() == $item_id) {
                $result = $item;
                break;
            }
            if ($item->has_children() && $result = $item->get_children()->get($item_id)) {
                break;
            }
        }
        return $result;
    }
    /**
     * Move menu item
     *
     * @param string $itemId
     * @param string $toItemId
     * @param int $sortIndex
     * @return void
     * @throws \InvalidArgumentException
     */
    public function move($item_id, $to_item_id, $sort_index = null)
    {
        $item = $this->get($item_id);
        if ($item === null) {
            throw new \InvalidArgumentException("Item with identifier {$item_id} does not exist");
        }
        $this->remove($item_id);
        $this->add($item, $to_item_id, $sort_index);
    }
    /**
     * Remove menu item by id
     *
     * @param string $itemId
     * @return bool
     */
    public function remove($item_id)
    {
        $result = false;
        /** @var Item $item */
        foreach ($this as $key => $item) {
            if ($item->get_id() == $item_id) {
                unset($this[$key]);
                $result = true;
                $this->_logger->debug(sprintf('Remove on item with id %s was processed', $item->get_id()));
                break;
            }
            if ($item->has_children() && $result = $item->get_children()->remove($item_id)) {
                break;
            }
        }
        return $result;
    }
    /**
     * Change order of an item in its parent menu
     *
     * @param string $itemId
     * @param int $position
     * @return bool
     */
    public function reorder($item_id, $position)
    {
        $result = false;
        /** @var Item $item */
        foreach ($this as $key => $item) {
            if ($item->get_id() == $item_id) {
                unset($this[$key]);
                $this->add($item, null, $position);
                $result = true;
                break;
            } elseif ($item->has_children() && $result = $item->get_children()->reorder($item_id, $position)) {
                break;
            }
        }
        return $result;
    }
    /**
     * Check whether provided item is last in list
     *
     * @param Item $item
     * @return bool
     */
    public function is_last(Item $item)
    {
        return $this->offsetGet(max(array_keys($this->get_array_copy())))->get_id() == $item->get_id();
    }
    /**
     * Find first menu item that user is able to access
     *
     * @return Item|null
     */
    public function get_first_available()
    {
        $result = null;
        /** @var Item $item */
        foreach ($this as $item) {
            if ($item->is_allowed() && !$item->is_disabled()) {
                if ($item->has_children()) {
                    $result = $item->get_children()->get_first_available();
                    if (false == ($result === null)) {
                        break;
                    }
                } else {
                    $result = $item;
                    break;
                }
            }
        }
        return $result;
    }
    /**
     * Get parent items by item id
     *
     * @param string $itemId
     * @return Item[]
     */
    public function get_parent_items($item_id)
    {
        $parents = [];
        $this->_find_parent_items($this, $item_id, $parents);
        return array_reverse($parents);
    }
    /**
     * Find parent items
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param string $itemId
     * @param array $parents
     * @return bool
     */
    protected function _find_parent_items($menu, $item_id, &$parents)
    {
        /** @var Item $item */
        foreach ($menu as $item) {
            if ($item->get_id() == $item_id) {
                return true;
            }
            if ($item->has_children()) {
                if ($this->_find_parent_items($item->get_children(), $item_id, $parents)) {
                    $parents[] = $item;
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Serialize menu
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function serialize()
    {
        return $this->serializer->serialize($this->to_array());
    }
    /**
     * Get menu data represented as an array
     *
     * @return array
     * @since 100.2.0
     */
    public function to_array()
    {
        $data = [];
        foreach ($this as $item) {
            $data[] = $item->to_array();
        }
        return $data;
    }
    /**
     * Unserialize menu
     *
     * @param string $serialized
     * @return void
     * @since 100.2.0
     */
    #[\Return_Type_Will_Change]
    public function unserialize($serialized)
    {
        $data = $this->serializer->unserialize($serialized);
        $this->populate_from_array($data);
    }
    /**
     * Populate the menu with data from array
     *
     * @param array $data
     * @return void
     * @since 100.2.0
     */
    public function populate_from_array(array $data)
    {
        $items = [];
        foreach ($data as $item_data) {
            $item = $this->menu_item_factory->create($item_data);
            $items[] = $item;
        }
        $this->exchange_array($items);
    }
}