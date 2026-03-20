<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Backend form container block
 *
 * @api
 * @deprecated 100.2.0 Use UI components for form rendering instead of this legacy form container
 * @see \Magento\Ui\Component\Form
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Container extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_object_id = 'id';
    /**
     * @var string[]
     */
    protected $_form_scripts = [];
    /**
     * @var string[]
     */
    protected $_form_init_scripts = [];
    /**
     * @var string
     */
    protected $_mode = 'edit';
    /**
     * @var string
     */
    protected $_block_group = 'Magento_Backend';
    /**
     * @var string
     */
    public const PARAM_BLOCK_GROUP = 'block_group';
    /**
     * @var string
     */
    public const PARAM_MODE = 'mode';
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/container.phtml';
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(Context $context, array $data = [], ?Secure_Html_Renderer $secure_renderer = null)
    {
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        parent::__construct($context, $data);
    }
    /**
     * Initialize form.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->has_data(self::PARAM_BLOCK_GROUP)) {
            $this->_block_group = $this->_get_data(self::PARAM_BLOCK_GROUP);
        }
        if ($this->has_data(self::PARAM_MODE)) {
            $this->_mode = $this->_get_data(self::PARAM_MODE);
        }
        $this->add_button('back', ['label' => __('Back'), 'onclick' => 'setLocation(\'' . $this->get_back_url() . '\')', 'class' => 'back'], -1);
        $this->add_button('reset', ['label' => __('Reset'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'], -1);
        $obj_id = (int) $this->get_request()->get_param($this->_object_id);
        if (!empty($obj_id)) {
            $confirm_message = $this->escape_js($this->escape_html(__('Are you sure you want to do this?')));
            $delete_on_click = 'deleteConfirm(\'' . $confirm_message . '\', \'' . $this->get_delete_url() . '\', {data: {}})';
            $this->add_button('delete', ['label' => __('Delete'), 'class' => 'delete', 'onclick' => $delete_on_click]);
        }
        $this->add_button('save', ['label' => __('Save'), 'class' => 'save primary', 'data_attribute' => ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]], 1);
    }
    /**
     * Create form block
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        if ($this->_block_group && $this->_controller && $this->_mode && !$this->_layout->get_child_name($this->_name_in_layout, 'form')) {
            $this->add_child('form', $this->_build_form_class_name());
        }
        return parent::_prepare_layout();
    }
    /**
     * Build child form class name
     *
     * @return string
     */
    protected function _build_form_class_name()
    {
        return $this->name_builder->build_class_name([$this->_block_group, 'Block', $this->_controller, $this->_mode, 'Form']);
    }
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function get_back_url()
    {
        return $this->get_url('*/*/');
    }
    /**
     * Get URL for delete button.
     *
     * @return string
     */
    public function get_delete_url()
    {
        return $this->get_url('*/*/delete', [$this->_object_id => (int) $this->get_request()->get_param($this->_object_id)]);
    }
    /**
     * Get form save URL
     *
     * @see getFormActionUrl()
     * @return string
     */
    public function get_save_url()
    {
        return $this->get_form_action_url();
    }
    /**
     * Get form action URL
     *
     * @return string
     */
    public function get_form_action_url()
    {
        if ($this->has_form_action_url()) {
            return $this->get_data('form_action_url');
        }
        return $this->get_url('*/*/save');
    }
    /**
     * Get form HTML.
     *
     * @return string
     */
    public function get_form_html()
    {
        $this->get_child_block('form')->set_data('action', $this->get_save_url());
        return $this->get_child_html('form');
    }
    /**
     * Get form init scripts.
     *
     * @return string
     */
    public function get_form_init_scripts()
    {
        if (!empty($this->_form_init_scripts) && is_array($this->_form_init_scripts)) {
            return $this->secure_renderer->render_tag('script', [], implode("\n", $this->_form_init_scripts), false);
        }
        return '';
    }
    /**
     * Get form scripts.
     *
     * @return string
     */
    public function get_form_scripts()
    {
        if (!empty($this->_form_scripts) && is_array($this->_form_scripts)) {
            return $this->secure_renderer->render_tag('script', [], implode("\n", $this->_form_scripts), false);
        }
        return '';
    }
    /**
     * Get header width.
     *
     * @return string
     */
    public function get_header_width()
    {
        return '';
    }
    /**
     * Get header css class.
     *
     * @return string
     */
    public function get_header_css_class()
    {
        return 'icon-head head-' . strtr($this->_controller, '_', '-');
    }
    /**
     * Get header HTML.
     *
     * @return string
     */
    public function get_header_html()
    {
        return '<h3 class="' . $this->get_header_css_class() . '">' . $this->get_header_text() . '</h3>';
    }
    /**
     * Set data object and pass it to form
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function set_data_object($object)
    {
        $this->get_child_block('form')->set_data_object($object);
        return $this->set_data('data_object', $object);
    }
}