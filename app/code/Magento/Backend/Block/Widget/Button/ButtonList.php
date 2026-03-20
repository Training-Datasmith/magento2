<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button;

/**
 * Button list widget
 *
 * @api
 * @since 100.0.2
 */
class Button_List
{
    /**
     * @var ItemFactory
     */
    protected $item_factory;
    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(Item_Factory $item_factory)
    {
        $this->item_factory = $item_factory;
    }
    /**
     * @var array
     */
    protected $_buttons = [-1 => [], 0 => [], 1 => []];
    /**
     * Add a button
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add($button_id, $data, $level = 0, $sort_order = 0, $region = 'toolbar')
    {
        if (!isset($this->_buttons[$level])) {
            $this->_buttons[$level] = [];
        }
        $data['id'] = empty($data['id']) ? $button_id : $data['id'];
        $data['button_key'] = $data['id'] . '_button';
        $data['region'] = empty($data['region']) ? $region : $data['region'];
        $data['level'] = $level;
        $sort_order = $sort_order ?: (count($this->_buttons[$level]) + 1) * 10;
        $data['sort_order'] = empty($data['sort_order']) ? $sort_order : $data['sort_order'];
        $this->_buttons[$level][$button_id] = $this->item_factory->create(['data' => $data]);
    }
    /**
     * Remove existing button
     *
     * @param string $buttonId
     * @return void
     */
    public function remove($button_id)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$button_id])) {
                /** @var Item $item */
                $item = $buttons[$button_id];
                $item->is_deleted(true);
                unset($this->_buttons[$level][$button_id]);
            }
        }
    }
    /**
     * Update specified button property
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return void
     */
    public function update($button_id, $key, $data)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$button_id])) {
                if (!empty($key)) {
                    if ('level' == $key) {
                        $this->_buttons[$data][$button_id] = $this->_buttons[$level][$button_id];
                        unset($this->_buttons[$level][$button_id]);
                    } else {
                        /** @var Item $item */
                        $item = $this->_buttons[$level][$button_id];
                        $item->set_data($key, $data);
                    }
                } else {
                    /** @var Item $item */
                    $item = $this->_buttons[$level][$button_id];
                    $item->set_data($data);
                }
                break;
            }
        }
    }
    /**
     * Get all buttons
     *
     * @return array
     */
    public function get_items()
    {
        array_walk($this->_buttons, function (&$item) {
            uasort($item, [$this, 'sortButtons']);
        });
        return $this->_buttons;
    }
    /**
     * Sort buttons by sort order
     *
     * @param Item $itemA
     * @param Item $itemB
     * @return int
     */
    public function sort_buttons(Item $item_a, Item $item_b)
    {
        return (int) $item_a->get_sort_order() <=> (int) $item_b->get_sort_order();
    }
}