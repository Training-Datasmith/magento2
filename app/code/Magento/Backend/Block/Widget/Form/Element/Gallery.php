<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Json\Helper\Data as JsonHelper;
/**
 * Backend image gallery item renderer
 */
class Gallery extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\Renderer_Interface
{
    /**
     * @var AbstractElement|null
     */
    protected $_element = null;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/element/gallery.phtml';
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $data['jsonHelper'] = Object_Manager::get_instance()->get(Json_Helper::class);
        parent::__construct($context, $data);
    }
    /**
     * Renderer.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(Abstract_Element $element)
    {
        $this->set_element($element);
        return $this->to_html();
    }
    /**
     * Set element.
     *
     * @param AbstractElement $element
     * @return $this
     */
    public function set_element(Abstract_Element $element)
    {
        $this->_element = $element;
        return $this;
    }
    /**
     * Get element.
     *
     * @return AbstractElement|null
     */
    public function get_element()
    {
        return $this->_element;
    }
    /**
     * Get value.
     *
     * @return array
     */
    public function get_values()
    {
        return $this->get_element()->get_value();
    }
    /**
     * @inheritdoc
     */
    protected function _prepare_layout()
    {
        $this->add_child('delete_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Delete'), 'onclick' => 'deleteImage(#image#)', 'class' => 'delete']);
        $this->add_child('add_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Add New Image'), 'onclick' => 'addNewImage()', 'class' => 'add']);
        return parent::_prepare_layout();
    }
    /**
     * Return add button.
     *
     * @return string
     */
    public function get_add_button_html()
    {
        return $this->get_child_html('add_button');
    }
    /**
     * Return delete button.
     *
     * @param string $image
     * @return string|string[]
     */
    public function get_delete_button_html($image)
    {
        return str_replace('#image#', $image, $this->get_child_html('delete_button'));
    }
}