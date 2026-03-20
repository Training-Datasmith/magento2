<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * Grid widget massaction single action item
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Extended
     */
    protected $_massaction = null;
    /**
     * Set parent massaction block
     *
     * @param  Extended $massaction
     * @return $this
     */
    public function set_massaction($massaction)
    {
        $this->_massaction = $massaction;
        return $this;
    }
    /**
     * Retrieve parent massaction block
     *
     * @return Extended
     */
    public function get_massaction()
    {
        return $this->_massaction;
    }
    /**
     * Set additional action block for this item
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function set_additional_action_block($block)
    {
        if (is_string($block)) {
            $block = $this->get_layout()->create_block($block);
        } elseif (is_array($block)) {
            $block = $this->_create_from_config($block);
        } elseif (!$block instanceof \Magento\Framework\View\Element\Abstract_Block) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Unknown block type'));
        }
        $this->set_child('additional_action', $block);
        return $this;
    }
    /**
     * @param array $config
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _create_from_config(array $config)
    {
        $type = isset($config['type']) ? $config['type'] : 'default';
        switch ($type) {
            default:
                $block_class = \Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\Default_Additional::class;
                break;
        }
        $block = $this->get_layout()->create_block($block_class);
        $block->create_from_configuration(isset($config['type']) ? $config['config'] : $config);
        return $block;
    }
    /**
     * Retrieve additional action block for this item
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function get_additional_action_block()
    {
        return $this->get_child_block('additional_action');
    }
    /**
     * Retrieve additional action block HTML for this item
     *
     * @return string
     */
    public function get_additional_action_block_html()
    {
        return $this->get_child_html('additional_action');
    }
}