<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button\Toolbar;

use Magento\Backend\Block\Widget\Button\Context_Interface;
/**
 * @method \Magento\Backend\Block\Widget\Button\Item getButtonItem()
 * @method ContextInterface getContext()
 * @method ContextInterface setContext(ContextInterface $context)
 * @api
 * @since 100.0.2
 */
class Container extends \Magento\Framework\View\Element\Abstract_Block
{
    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return \Magento\Backend\Block\Widget\Button
     */
    protected function create_button($block_name, $block_class_name = null)
    {
        if (null === $block_class_name) {
            $block_class_name = \Magento\Backend\Block\Widget\Button::class;
        }
        return $this->get_layout()->create_block($block_class_name, $block_name);
    }
    /**
     * {@inheritdoc}
     */
    protected function _to_html()
    {
        $item = $this->get_button_item();
        $context = $this->get_context();
        if ($item && $context && $context->can_render($item)) {
            $data = $item->get_data();
            $block_class_name = isset($data['class_name']) ? $data['class_name'] : null;
            $button_name = $this->get_context()->get_name_in_layout() . '-' . $item->get_id() . '-button';
            $block = $this->create_button($button_name, $block_class_name);
            $block->set_data($data);
            return $block->to_html();
        }
        return parent::_to_html();
    }
}