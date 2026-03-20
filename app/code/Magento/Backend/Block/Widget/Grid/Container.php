<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * Backend grid container block
 *
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Container extends \Magento\Backend\Block\Widget\Container
{
    /**#@+
     * Initialization parameters in pseudo-constructor
     */
    public const PARAM_BLOCK_GROUP = 'block_group';
    public const PARAM_BUTTON_NEW = 'button_new';
    public const PARAM_BUTTON_BACK = 'button_back';
    /**#@-*/
    /**#@-*/
    protected $_add_button_label;
    /**
     * @var string
     */
    protected $_back_button_label;
    /**
     * @var string
     */
    protected $_block_group = 'Magento_Backend';
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/container.phtml';
    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->has_data(self::PARAM_BLOCK_GROUP)) {
            $this->_block_group = $this->_get_data(self::PARAM_BLOCK_GROUP);
        }
        if ($this->has_data(self::PARAM_BUTTON_NEW)) {
            $this->_add_button_label = $this->_get_data(self::PARAM_BUTTON_NEW);
        } else {
            // legacy logic to support all descendants
            if ($this->_add_button_label === null) {
                $this->_add_button_label = __('Add New');
            }
            $this->_add_new_button();
        }
        if ($this->has_data(self::PARAM_BUTTON_BACK)) {
            $this->_back_button_label = $this->_get_data(self::PARAM_BUTTON_BACK);
        } else if ($this->_back_button_label === null) {
            $this->_back_button_label = __('Back');
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_layout()
    {
        // check if grid was created through the layout
        if (false === $this->get_child_block('grid')) {
            $this->set_child('grid', $this->get_layout()->create_block(str_replace('_', '\\', $this->_block_group) . '\Block\\' . str_replace(' ', '\\', ucwords(str_replace('_', ' ', $this->_controller))) . '\Grid', $this->_controller . '.grid')->set_save_parameters_in_session(true));
        }
        return parent::_prepare_layout();
    }
    /**
     * @return string
     */
    public function get_create_url()
    {
        return $this->get_url('*/*/new');
    }
    /**
     * @return string
     */
    public function get_grid_html()
    {
        return $this->get_child_html('grid');
    }
    /**
     * @return string
     */
    public function get_add_button_label()
    {
        return $this->_add_button_label;
    }
    /**
     * @return string
     */
    public function get_back_button_label()
    {
        return $this->_back_button_label;
    }
    /**
     * Create "New" button
     *
     * @return void
     */
    protected function _add_new_button()
    {
        $this->add_button('add', ['label' => $this->get_add_button_label(), 'onclick' => 'setLocation(\'' . $this->get_create_url() . '\')', 'class' => 'add primary']);
    }
    /**
     * @return void
     */
    protected function _add_back_button()
    {
        $this->add_button('back', ['label' => $this->get_back_button_label(), 'onclick' => 'setLocation(\'' . $this->get_back_url() . '\')', 'class' => 'back']);
    }
    /**
     * {@inheritdoc}
     */
    public function get_header_css_class()
    {
        return 'icon-head ' . parent::get_header_css_class();
    }
    /**
     * @return string
     */
    public function get_header_width()
    {
        return 'width:50%;';
    }
}