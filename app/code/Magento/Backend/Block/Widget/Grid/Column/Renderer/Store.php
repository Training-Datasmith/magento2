<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Store grid column filter
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var bool
     */
    protected $_skip_all_stores_label = false;
    /**
     * @var bool
     */
    protected $_skip_empty_stores_label = false;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_system_store;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Store\Model\System\Store $system_store, array $data = [])
    {
        $this->_system_store = $system_store;
        parent::__construct($context, $data);
    }
    /**
     * Retrieve System Store model
     *
     * @return \Magento\Store\Model\System\Store
     */
    protected function _get_store_model()
    {
        return $this->_system_store;
    }
    /**
     * Retrieve 'show all stores label' flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _get_show_all_stores_label_flag()
    {
        return $this->get_column()->get_data('skipAllStoresLabel') ? $this->get_column()->get_data('skipAllStoresLabel') : $this->_skip_all_stores_label;
    }
    /**
     * Retrieve 'show empty stores label' flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _get_show_empty_stores_label_flag()
    {
        return $this->get_column()->get_data('skipEmptyStoresLabel') ? $this->get_column()->get_data('skipEmptyStoresLabel') : $this->_skip_empty_stores_label;
    }
    /**
     * Render row store views
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $out = '';
        $skip_all_stores_label = $this->_get_show_all_stores_label_flag();
        $skip_empty_stores_label = $this->_get_show_empty_stores_label_flag();
        $orig_stores = $row->get_data($this->get_column()->get_index());
        if ($orig_stores === null && $row->get_store_name()) {
            $scopes = [];
            foreach (explode("\n", $row->get_store_name()) as $k => $label) {
                $scopes[] = str_repeat('&nbsp;', $k * 3) . $label;
            }
            $out .= implode('<br/>', $scopes) . __(' [deleted]');
            return $out;
        }
        if (empty($orig_stores) && !$skip_empty_stores_label) {
            return '';
        }
        if (!is_array($orig_stores)) {
            $orig_stores = [$orig_stores];
        }
        if (empty($orig_stores)) {
            return '';
        } elseif (in_array(0, $orig_stores) && count($orig_stores) == 1 && !$skip_all_stores_label) {
            return __('All Store Views');
        }
        $data = $this->_get_store_model()->get_stores_structure(false, $orig_stores);
        foreach ($data as $website) {
            $out .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $out .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $out;
    }
    /**
     * Render row store views for export
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function render_export(\Magento\Framework\Data_Object $row)
    {
        $out = '';
        $skip_all_stores_label = $this->_get_show_all_stores_label_flag();
        $orig_stores = $row->get_data($this->get_column()->get_index());
        if ($orig_stores === null && $row->get_store_name()) {
            $scopes = [];
            foreach (explode("\n", $row->get_store_name()) as $k => $label) {
                $scopes[] = str_repeat(' ', $k * 3) . $label;
            }
            $out .= implode("\r\n", $scopes) . __(' [deleted]');
            return $out;
        }
        if (!is_array($orig_stores)) {
            $orig_stores = [$orig_stores];
        }
        if (in_array(0, $orig_stores) && !$skip_all_stores_label) {
            return __('All Store Views');
        }
        $data = $this->_get_store_model()->get_stores_structure(false, $orig_stores);
        foreach ($data as $website) {
            $out .= $website['label'] . "\r\n";
            foreach ($website['children'] as $group) {
                $out .= str_repeat(' ', 3) . $group['label'] . "\r\n";
                foreach ($group['children'] as $store) {
                    $out .= str_repeat(' ', 6) . $store['label'] . "\r\n";
                }
            }
        }
        return $out;
    }
}