<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Store grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class Store extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    public const ALL_STORE_VIEWS = '0';
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_system_store;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, \Magento\Store\Model\System\Store $system_store, array $data = [])
    {
        $this->_system_store = $system_store;
        parent::__construct($context, $resource_helper, $data);
    }
    /**
     * Render HTML of the element
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_html()
    {
        $website_collection = $this->_system_store->get_website_collection();
        $group_collection = $this->_system_store->get_group_collection();
        $store_collection = $this->_system_store->get_store_collection();
        $all_show = $this->get_column()->get_store_all();
        $html = '<select class="admin__control-select" name="' . $this->escape_html($this->_get_html_name()) . '" ' . $this->get_column()->get_validate_class() . $this->get_ui_id('filter', $this->_get_html_name()) . '>';
        $value = $this->get_column()->get_value();
        if ($all_show) {
            $html .= '<option value="' . self::ALL_STORE_VIEWS . '"' . ($value == self::ALL_STORE_VIEWS ? ' selected="selected"' : '') . '>' . __('All Store Views') . '</option>';
        } else {
            $html .= '<option value=""' . (!$value ? ' selected="selected"' : '') . '></option>';
        }
        foreach ($website_collection as $website) {
            $website_show = false;
            foreach ($group_collection as $group) {
                if ($group->get_website_id() != $website->get_id()) {
                    continue;
                }
                $group_show = false;
                foreach ($store_collection as $store) {
                    if ($store->get_group_id() != $group->get_id()) {
                        continue;
                    }
                    if (!$website_show) {
                        $website_show = true;
                        $html .= '<optgroup label="' . $this->escape_html($website->get_name()) . '"></optgroup>';
                    }
                    if (!$group_show) {
                        $group_show = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $this->escape_html($group->get_name()) . '">';
                    }
                    $value = $this->get_value();
                    $selected = $value == $store->get_id() ? ' selected="selected"' : '';
                    $html .= '<option value="' . $store->get_id() . '"' . $selected . '>&nbsp;&nbsp;&nbsp;&nbsp;' . $this->escape_html($store->get_name()) . '</option>';
                }
                if ($group_show) {
                    $html .= '</optgroup>';
                }
            }
        }
        if ($this->get_column()->get_display_deleted()) {
            $selected = $this->get_value() == '_deleted_' ? ' selected' : '';
            $html .= '<option value="_deleted_"' . $selected . '>' . __('[ deleted ]') . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    /**
     * Form condition from element's value
     *
     * @return array|null
     */
    public function get_condition()
    {
        $value = $this->get_value();
        if ($value === null || $value == self::ALL_STORE_VIEWS) {
            return null;
        }
        if ($value == '_deleted_') {
            return ['null' => true];
        } else {
            return ['eq' => $value];
        }
    }
}