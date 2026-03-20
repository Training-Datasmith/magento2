<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Widget\Grid\Massaction;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column_Set;
use Magento\Backend\Block\Widget\Grid\Massaction\Visibility_Checker_Interface as VisibilityChecker;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Data_Object;
use Magento\Framework\DB\Select;
use Magento\Framework\Json\Encoder_Interface;
use Magento\Quote\Model\Quote;
/**
 * Grid widget massaction block
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @method Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
abstract class Abstract_Massaction extends Widget
{
    /**
     * @var EncoderInterface
     */
    protected $_json_encoder;
    /**
     * Massaction items
     *
     * @var array
     */
    protected $_items = [];
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/massaction.phtml';
    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(Context $context, Encoder_Interface $json_encoder, array $data = [])
    {
        $this->_json_encoder = $json_encoder;
        parent::__construct($context, $data);
    }
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_error_text($this->escape_html(__('An item needs to be selected. Select and try again.')));
        if (null !== $this->get_options()) {
            foreach ($this->get_options() as $option_id => $option) {
                $this->add_item($option_id, $option);
            }
            $this->unset_data('options');
        }
    }
    /**
     * Add new massaction item
     *
     * Item array to be passed in looks like:
     * $item = array(
     *      'label'    => string,
     *      'complete' => string, // Only for ajax enabled grid (optional)
     *      'url'      => string,
     *      'confirm'  => string, // text of confirmation of this action (optional)
     *      'additional' => string, // (optional)
     *      'visible' => object // instance of VisibilityCheckerInterface (optional)
     * );
     *
     * @param string $itemId
     * @param array|DataObject $item
     * @return $this
     */
    public function add_item($item_id, $item)
    {
        if (is_array($item)) {
            $item = new Data_Object($item);
        }
        if ($item instanceof Data_Object && $this->is_visible($item)) {
            $item->set_id($item_id);
            $item->set_url($this->get_url($item->get_url()));
            $this->_items[$item_id] = $item;
        }
        return $this;
    }
    /**
     * Check that item can be added to list
     *
     * @param DataObject $item
     * @return bool
     */
    private function is_visible(Data_Object $item)
    {
        /** @var VisibilityChecker $checker */
        $checker = $item->get_data('visible');
        return !$checker instanceof Visibility_Checker || $checker->is_visible();
    }
    /**
     * Retrieve massaction item with id $itemId
     *
     * @param string $itemId
     * @return \Magento\Backend\Block\Widget\Grid\Massaction\Item|null
     */
    public function get_item($item_id)
    {
        return $this->_items[$item_id] ?? null;
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
        return $this->get_count() > 0 && $this->get_massaction_id_field();
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
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
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
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
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
     * Get mass action javascript code.
     *
     * @return string
     */
    public function get_java_script()
    {
        return " {$this->get_js_object_name()} = new varienGridMassaction('{$this->get_html_id()}', " . "{$this->get_grid_js_object_name()}, '{$this->get_selected_json()}'" . ", '{$this->get_form_field_name_internal()}', '{$this->get_form_field_name()}');" . "{$this->get_js_object_name()}.setItems({$this->get_items_json()}); " . "{$this->get_js_object_name()}.setGridIds('{$this->get_grid_ids_json()}');" . ($this->get_use_ajax() ? "{$this->get_js_object_name()}.setUseAjax(true);" : '') . ($this->get_use_select_all() ? "{$this->get_js_object_name()}.setUseSelectAll(true);" : '') . "{$this->get_js_object_name()}.errorText = '{$this->get_error_text()}';" . "\n" . "window.{$this->get_js_object_name()} = {$this->get_js_object_name()};";
    }
    /**
     * Get grid ids in JSON format.
     *
     * @return string
     */
    public function get_grid_ids_json()
    {
        if (!$this->get_use_select_all()) {
            return '';
        }
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = clone $this->get_parent_block()->get_collection();
        if ($collection instanceof Abstract_Db) {
            $ids_select = clone $collection->get_select();
            $ids_select->reset(Select::ORDER);
            $ids_select->reset(Select::LIMIT_COUNT);
            $ids_select->reset(Select::LIMIT_OFFSET);
            $ids_select->reset(Select::COLUMNS);
            $ids_select->columns($this->get_massaction_id_field());
            $id_list = $collection->get_connection()->fetch_col($ids_select);
        } else {
            $id_list = $collection->set_page_size(0)->get_column_values($this->get_massaction_id_field());
        }
        return implode(',', $id_list);
    }
    /**
     * Get Html id.
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
    /**
     * Prepare grid massaction column
     *
     * @return $this
     */
    public function prepare_massaction_column()
    {
        $column_id = 'massaction';
        $massaction_column = $this->get_layout()->create_block(Column::class)->set_data(['index' => $this->get_massaction_id_field(), 'filter_index' => $this->get_massaction_id_filter(), 'type' => 'massaction', 'name' => $this->get_form_field_name(), 'is_system' => true, 'header_css_class' => 'col-select', 'column_css_class' => 'col-select']);
        if ($this->get_no_filter_massaction_column()) {
            $massaction_column->set_data('filter', false);
        }
        $grid_block = $this->get_parent_block();
        $massaction_column->set_selected($this->get_selected())->set_grid($grid_block)->set_id($column_id);
        /** @var $columnSetBlock ColumnSet */
        $column_set_block = $grid_block->get_column_set();
        $child_names = $column_set_block->get_child_names();
        $sibling_element = count($child_names) ? current($child_names) : 0;
        $column_set_block->insert($massaction_column, $sibling_element, false, $column_id);
        return $this;
    }
}