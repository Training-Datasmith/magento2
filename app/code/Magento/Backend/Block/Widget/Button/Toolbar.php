<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button;

use Magento\Framework\View\Layout_Interface;
class Toolbar implements Toolbar_Interface
{
    /**
     * {@inheritdoc}
     */
    public function push_buttons(\Magento\Framework\View\Element\Abstract_Block $context, \Magento\Backend\Block\Widget\Button\Button_List $button_list)
    {
        foreach ($button_list->get_items() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                $container_name = $context->get_name_in_layout() . '-' . $item->get_button_key();
                $container = $this->create_container($context->get_layout(), $container_name, $item);
                if ($item->has_data('name')) {
                    $item->set_data('element_name', $item->get_name());
                }
                if ($container) {
                    $container->set_context($context);
                    $toolbar = $this->get_toolbar($context, $item->get_region());
                    $toolbar->set_child($item->get_button_key(), $container);
                }
            }
        }
    }
    /**
     * Create button container
     *
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param string $containerName
     * @param \Magento\Backend\Block\Widget\Button\Item $buttonItem
     * @return \Magento\Backend\Block\Widget\Button\Toolbar\Container
     */
    protected function create_container(Layout_Interface $layout, $container_name, $button_item)
    {
        $container = $layout->create_block(\Magento\Backend\Block\Widget\Button\Toolbar\Container::class, $container_name, ['data' => ['button_item' => $button_item]]);
        return $container;
    }
    /**
     * Return button parent block
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param string $region
     * @return \Magento\Backend\Block\Template
     */
    protected function get_toolbar(\Magento\Framework\View\Element\Abstract_Block $context, $region)
    {
        $parent = null;
        $layout = $context->get_layout();
        if (!$region || $region == 'header' || $region == 'footer') {
            $parent = $context;
        } elseif ($region == 'toolbar') {
            $parent = $layout->get_block('page.actions.toolbar');
        } else {
            $parent = $layout->get_block($region);
        }
        if ($parent) {
            return $parent;
        }
        return $context;
    }
}