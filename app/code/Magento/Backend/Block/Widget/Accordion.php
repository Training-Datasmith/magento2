<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Magento_Backend accordion widget
 *
 * @api
 * @since 100.0.2
 */
class Accordion extends \Magento\Backend\Block\Widget
{
    /**
     * @var string[]
     */
    protected $_items = [];
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/accordion.phtml';
    /**
     * @return string[]
     */
    public function get_items()
    {
        return $this->_items;
    }
    /**
     * @param string $itemId
     * @param array $config
     * @return $this
     */
    public function add_item($item_id, $config)
    {
        $this->_items[$item_id] = $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Accordion\Item::class, $this->get_name_in_layout() . '-' . $item_id)->set_data($config)->set_accordion($this)->set_id($item_id);
        if (isset($config['content']) && $config['content'] instanceof \Magento\Framework\View\Element\Abstract_Block) {
            $this->_items[$item_id]->set_child($item_id . '_content', $config['content']);
        }
        $this->set_child($item_id, $this->_items[$item_id]);
        return $this;
    }
}