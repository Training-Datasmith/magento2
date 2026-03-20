<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Theme grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Theme grid filter
 */
class Theme extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_label_factory;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, \Magento\Framework\View\Design\Theme\Label_Factory $label_factory, array $data = [])
    {
        $this->_label_factory = $label_factory;
        parent::__construct($context, $resource_helper, $data);
    }
    /**
     * Retrieve filter HTML
     *
     * @return string
     */
    public function get_html()
    {
        $options = $this->get_options();
        if ($this->get_column()->get_with_empty()) {
            array_unshift($options, ['value' => '', 'label' => '']);
        }
        $html = sprintf('<select name="%s" id="%s" class="admin__control-select no-changes" %s>%s</select>', $this->_get_html_name(), $this->_get_html_id(), $this->get_ui_id('filter', $this->_get_html_name()), $this->_draw_options($options));
        return $html;
    }
    /**
     * Retrieve options set in column.
     *
     * Or load if options was not set.
     *
     * @return array
     */
    public function get_options()
    {
        $options = $this->get_column()->get_options();
        if (empty($options) || !is_array($options)) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $label = $this->_label_factory->create();
            $options = $label->get_labels_collection();
        }
        return $options;
    }
    /**
     * Render SELECT options
     *
     * @param array $options
     * @return string
     */
    protected function _draw_options($options)
    {
        if (empty($options) || !is_array($options)) {
            return '';
        }
        $value = $this->get_value();
        $html = '';
        foreach ($options as $option) {
            if (!isset($option['value']) || !isset($option['label'])) {
                continue;
            }
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $option['label'] . '">' . $this->_draw_options($option['value']) . '</optgroup>';
            } else {
                $selected = $option['value'] == $value && $value !== null ? ' selected="selected"' : '';
                $html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['label'] . '</option>';
            }
        }
        return $html;
    }
    /**
     * Retrieve filter condition for collection
     *
     * @return mixed
     */
    public function get_condition()
    {
        if ($this->get_value() === null) {
            return null;
        }
        $value = $this->get_value();
        if ($value == 'all') {
            $value = '';
        }
        return ['eq' => $value];
    }
}