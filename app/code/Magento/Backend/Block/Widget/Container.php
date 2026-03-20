<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button\Button_List;
use Magento\Backend\Block\Widget\Button\Item as ButtonItem;
/**
 * Backend container block
 *
 * @api
 * @since 100.0.2
 */
class Container extends Template implements Container_Interface
{
    /**
     * Initialization parameters in pseudo-constructor
     */
    public const PARAM_CONTROLLER = 'controller';
    public const PARAM_HEADER_TEXT = 'header_text';
    /**
     * @var string
     */
    protected $_controller = 'empty';
    /**
     * @var string
     */
    protected $_header_text = 'Container Widget Header';
    /**
     * @var ButtonList
     */
    protected $button_list;
    /**
     * @var Button\ToolbarInterface
     */
    protected $toolbar;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->button_list = $context->get_button_list();
        $this->toolbar = $context->get_button_toolbar();
        parent::__construct($context, $data);
    }
    /**
     * Initialize "controller" and "header text"
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->has_data(self::PARAM_CONTROLLER)) {
            $this->_controller = $this->_get_data(self::PARAM_CONTROLLER);
        }
        if ($this->has_data(self::PARAM_HEADER_TEXT)) {
            $this->_header_text = $this->_get_data(self::PARAM_HEADER_TEXT);
        }
    }
    /**
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return $this
     */
    public function add_button($button_id, $data, $level = 0, $sort_order = 0, $region = 'toolbar')
    {
        $this->button_list->add($button_id, $data, $level, $sort_order, $region);
        return $this;
    }
    /**
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @return $this
     */
    public function remove_button($button_id)
    {
        $this->button_list->remove($button_id);
        return $this;
    }
    /**
     * Public wrapper for protected _updateButton method
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return $this
     */
    public function update_button($button_id, $key, $data)
    {
        $this->button_list->update($button_id, $key, $data);
        return $this;
    }
    /**
     * Preparing child blocks for each added button
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->toolbar->push_buttons($this, $this->button_list);
        return parent::_prepare_layout();
    }
    /**
     * Produce buttons HTML
     *
     * @param string $region
     * @return string
     */
    public function get_buttons_html($region = null)
    {
        $out = '';
        foreach ($this->button_list->get_items() as $buttons) {
            /** @var ButtonItem $item */
            foreach ($buttons as $item) {
                if ($region && $region != $item->get_region()) {
                    continue;
                }
                $out .= $this->get_child_html($item->get_button_key());
            }
        }
        return $out;
    }
    /**
     * Get header text
     *
     * @return string
     */
    public function get_header_text()
    {
        return $this->_header_text;
    }
    /**
     * Get header CSS class
     *
     * @return string
     */
    public function get_header_css_class()
    {
        return 'head-' . strtr($this->_controller ?? '', '_', '-');
    }
    /**
     * Get header HTML
     *
     * @return string
     */
    public function get_header_html()
    {
        return '<h3 class="' . $this->get_header_css_class() . '">' . $this->get_header_text() . '</h3>';
    }
    /**
     * Check if there's anything to display in footer
     *
     * @return boolean
     */
    public function has_footer_buttons()
    {
        foreach ($this->button_list->get_items() as $buttons) {
            foreach ($buttons as $data) {
                if (isset($data['region']) && 'footer' == $data['region']) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Check whether button rendering is allowed in current context
     *
     * @param ButtonItem $item
     * @return bool
     */
    public function can_render(Button\Item $item)
    {
        return !$item->is_deleted();
    }
}