<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Additional extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\Options
     */
    protected $_options_interpreter;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\View\Layout\Argument\Interpreter\Options $optionsInterpreter
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Framework\View\Layout\Argument\Interpreter\Options $options_interpreter, array $data = [])
    {
        parent::__construct($context, $registry, $form_factory, $data);
        $this->_options_interpreter = $options_interpreter;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepare_form()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create();
        foreach ($this->get_data('fields') as $item_id => $item) {
            $this->_prepare_form_item($item);
            $form->add_field($item_id, $item['type'], $item);
        }
        $this->set_form($form);
        return $this;
    }
    /**
     * Prepare form item
     *
     * @param array &$item
     * @return void
     */
    protected function _prepare_form_item(array &$item)
    {
        if ($item['type'] == 'select' && is_string($item['values'])) {
            $model_class = $item['values'];
            $item['values'] = $this->_options_interpreter->evaluate(['model' => $model_class]);
        }
        $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
    }
}