<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Framework\View;
/**
 * @api
 * @since 100.0.2
 */
class Page extends View\Result\Page
{
    /**
     * Define active menu item in menu block
     *
     * @param string $itemId current active menu item
     * @return $this
     */
    public function set_active_menu($item_id)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menu_block = $this->layout->get_block('menu');
        $menu_block->set_active($item_id);
        $parents = $menu_block->get_menu_model()->get_parent_items($item_id);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            $this->get_config()->get_title()->prepend($item->get_title());
        }
        return $this;
    }
    /**
     * Add link to breadcrumb block
     *
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    public function add_breadcrumb($label, $title, $link = null)
    {
        /** @var \Magento\Backend\Block\Widget\Breadcrumbs $block */
        $block = $this->layout->get_block('breadcrumbs');
        if ($block) {
            $block->add_link($label, $title, $link);
        }
        return $this;
    }
    /**
     * Add content to content section
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function add_content(View\Element\Abstract_Block $block)
    {
        return $this->move_block_to_container($block, 'content');
    }
    /**
     * Add block to left container
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function add_left(View\Element\Abstract_Block $block)
    {
        return $this->move_block_to_container($block, 'left');
    }
    /**
     * Add javascript to head
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function add_js(View\Element\Abstract_Block $block)
    {
        return $this->move_block_to_container($block, 'js');
    }
    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param string $containerName
     * @return $this
     */
    protected function move_block_to_container(View\Element\Abstract_Block $block, $container_name)
    {
        $this->layout->set_child($container_name, $block->get_name_in_layout(), '');
        return $this;
    }
}