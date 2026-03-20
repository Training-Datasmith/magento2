<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\DB\Select;
/**
 * Grid widget massaction block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @see Nothing
 * @method \Magento\Quote\Model\Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @TODO MAGETWO-31510: Remove deprecated class
 * @since 100.0.2
 */
class Extended extends \Magento\Backend\Block\Widget
{
    /**
     * Massaction items
     *
     * @var array
     */
    protected $_items = [];
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/massaction_extended.phtml';
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backend_data = null;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_json_encoder;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Backend\Helper\Data $backend_data, array $data = [])
    {
        $this->_json_encoder = $json_encoder;
        $this->_backend_data = $backend_data;
        parent::__construct($context, $data);
    }
    /**
     * Sets Massaction template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->set_error_text($this->escape_html(__('An item needs to be selected. Select and try again.')));
    }
    /**
     * Add new massaction item
     *
     * The item array should look like:
     * $item = array(
     *      'label'    => string,
     *      'complete' => string, // Only for ajax enabled grid (optional)
     *      'url'      => string,
     *      'confirm'  => string, // text of confirmation of this action (optional)
     *      'additional' => string|array|\Magento\Framework\View\Element\AbstractBlock // (optional)
     * );
     *
     * @param string $itemId
     * @param array $item
     * @return $this
     */
    public function add_item($item_id, array $item)
    {
        $this->_items[$item_id] = $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Grid\Massaction\Item::class)->set_data($item)->set_massaction($this)->set_id($item_id);
        if ($this->_items[$item_id]->get_additional()) {
            $this->_items[$item_id]->set_additional_action_block($this->_items[$item_id]->get_additional());
            $this->_items[$item_id]->uns_additional();
        }
        return $this;
    }
    /**
     * Retrieve massaction item with id $itemId
     *
     * @param string $itemId
     * @return \Magento\Backend\Block\Widget\Grid\Massaction\Item|null
     */
    public function get_item($item_id)
    {
        if (isset($this->_items[$item_id])) {
            return $this->_items[$item_id];
        }
        return null;
    }
    /**
     * Retrieve massaction items
     *
     * @return array
     */
    public function get_items()
    {
        return $this->_items;
    }
    /**
     * Retrieve massaction items JSON
     *
     * @return string
     */
    public function get_items_json()
    {
        $result = [];
        foreach ($this->get_items() as $item_id => $item) {
            $result[$item_id] = $item->to_array();
        }
        return $this->_json_encoder->encode($result);
    }
    /**
     * Retrieve massaction items count
     *
     * @return integer
     */
    public function get_count()
    {
        return count($this->_items);
    }
    /**
     * Checks are massactions available
     *
     * @return boolean
     */
    public function is_available()
    {
        return $this->get_count() > 0 && $this->get_parent_block()->get_massaction_id_field();
    }
    /**
     * Retrieve global form field name for all massaction items
     *
     * @return string
     */
    public function get_form_field_name()
    {
        return $this->get_data('form_field_name') ? $this->get_data('form_field_name') : 'massaction';
    }
    /**
     * Retrieve form field name for internal use. Based on $this->getFormFieldName()
     *
     * @return string
     */
    public function get_form_field_name_internal()
    {
        return 'internal_' . $this->get_form_field_name();
    }
    /**
     * Retrieve massaction block js object name
     *
     * @return string
     */
    public function get_js_object_name()
    {
        return $this->get_html_id() . 'JsObject';
    }
    /**
     * Retrieve grid block js object name
     *
     * @return string
     */
    public function get_grid_js_object_name()
    {
        return $this->get_parent_block()->get_js_object_name();
    }
    /**
     * Retrieve JSON string of selected checkboxes
     *
     * @return string
     */
    public function get_selected_json()
    {
        if ($selected = $this->get_request()->get_param($this->get_form_field_name_internal())) {
            $selected = explode(',', $selected);
            return join(',', $selected);
        }
        return '';
    }
    /**
     * Retrieve array of selected checkboxes
     *
     * @return string[]
     */
    public function get_selected()
    {
        if ($selected = $this->get_request()->get_param($this->get_form_field_name_internal())) {
            $selected = explode(',', $selected);
            return $selected;
        }
        return [];
    }
    /**
     * Retrieve apply button html
     *
     * @return string
     */
    public function get_apply_button_html()
    {
        return $this->get_button_html(__('Submit'), $this->get_js_object_name() . '.apply()');
    }
    /**
     * Get mass action javascript code
     *
     * @return string
     */
    public function get_java_script()
    {
        return " {$this->get_js_object_name()} = new varienGridMassaction('{$this->get_html_id()}', " . "{$this->get_grid_js_object_name()}, '{$this->get_selected_json()}'" . ", '{$this->get_form_field_name_internal()}', '{$this->get_form_field_name()}');" . "{$this->get_js_object_name()}.setItems({$this->get_items_json()}); " . "{$this->get_js_object_name()}.setGridIds('{$this->get_grid_ids_json()}');" . ($this->get_use_ajax() ? "{$this->get_js_object_name()}.setUseAjax(true);" : '') . ($this->get_use_select_all() ? "{$this->get_js_object_name()}.setUseSelectAll(true);" : '') . "{$this->get_js_object_name()}.errorText = '{$this->get_error_text()}';" . "\n" . "window.{$this->get_js_object_name()} = {$this->get_js_object_name()};";
    }
    /**
     * Get grid ids in JSON format
     *
     * @return string
     */
    public function get_grid_ids_json()
    {
        if (!$this->get_use_select_all()) {
            return '';
        }
        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $all_ids_collection = clone $this->get_parent_block()->get_collection();
        if ($this->get_massaction_id_field()) {
            $mass_action_id_field = $this->get_massaction_id_field();
        } else {
            $mass_action_id_field = $this->get_parent_block()->get_massaction_id_field();
        }
        if ($all_ids_collection instanceof Abstract_Db) {
            $ids_select = clone $all_ids_collection->get_select();
            $ids_select->reset(Select::ORDER);
            $ids_select->reset(Select::LIMIT_COUNT);
            $ids_select->reset(Select::LIMIT_OFFSET);
            $ids_select->reset(Select::COLUMNS);
            $ids_select->columns($mass_action_id_field);
            $id_list = $all_ids_collection->get_connection()->fetch_col($ids_select);
        } else {
            $id_list = $all_ids_collection->set_page_size(0)->get_column_values($mass_action_id_field);
        }
        return implode(',', $id_list);
    }
    /**
     * Retrieve massaction block js object name
     *
     * @return string
     */
    public function get_html_id()
    {
        return $this->get_parent_block()->get_html_id() . '_massaction';
    }
    /**
     * Remove existing massaction item by its id
     *
     * @param string $itemId
     * @return $this
     */
    public function remove_item($item_id)
    {
        if (isset($this->_items[$item_id])) {
            unset($this->_items[$item_id]);
        }
        return $this;
    }
    /**
     * Retrieve select all functionality flag check
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_use_select_all()
    {
        return $this->_get_data('use_select_all') === null || $this->_get_data('use_select_all');
    }
    /**
     * Retrieve select all functionality flag check
     *
     * @param boolean $flag
     * @return $this
     */
    public function set_use_select_all($flag)
    {
        $this->set_data('use_select_all', (bool) $flag);
        return $this;
    }
}