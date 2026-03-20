<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Column_Set extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Backend\Model\Widget\Grid\Row\UrlGenerator
     */
    protected $_row_url_generator;
    /**
     * Column headers visibility
     *
     * @var boolean
     */
    protected $_headers_visibility = true;
    /**
     * Filter visibility
     *
     * @var boolean
     */
    protected $_filter_visibility = true;
    /**
     * Empty grid text
     *
     * @var string|null
     */
    protected $_empty_text;
    /**
     * Empty grid text CSS class
     *
     * @var string
     */
    protected $_empty_text_css = 'empty-text';
    /**
     * Label for empty cell
     *
     * @var string
     */
    protected $_empty_cell_label = '';
    /**
     * Count subtotals
     *
     * @var boolean
     */
    protected $_count_sub_totals = false;
    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_count_totals = false;
    /**
     * Columns to group by
     *
     * @var string[]
     */
    protected $_grouped_column = [];
    /**
     * @var boolean
     */
    protected $_is_collapsed;
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/column_set.phtml';
    /**
     * @var \Magento\Backend\Model\Widget\Grid\SubTotals
     */
    protected $_sub_totals = null;
    /**
     * @var \Magento\Backend\Model\Widget\Grid\Totals
     */
    protected $_totals = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory
     * @param \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals
     * @param \Magento\Backend\Model\Widget\Grid\Totals $totals
     * @param array $data
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Backend\Model\Widget\Grid\Row\Url_Generator_Factory $generator_factory, \Magento\Backend\Model\Widget\Grid\Sub_Totals $subtotals, \Magento\Backend\Model\Widget\Grid\Totals $totals, array $data = [])
    {
        $generator_class_name = \Magento\Backend\Model\Widget\Grid\Row\Url_Generator::class;
        if (isset($data['rowUrl'])) {
            $row_url_params = $data['rowUrl'];
            if (isset($row_url_params['generatorClass'])) {
                $generator_class_name = $row_url_params['generatorClass'];
            }
            $this->_row_url_generator = $generator_factory->create_url_generator($generator_class_name, ['args' => $row_url_params]);
        }
        $this->set_filter_visibility(array_key_exists('filter_visibility', $data) ? (bool) $data['filter_visibility'] : true);
        parent::__construct($context, $data);
        $this->set_empty_text(isset($data['empty_text']) ? $data['empty_text'] : __('We couldn\'t find any records.'));
        $this->set_empty_cell_label(isset($data['empty_cell_label']) ? $data['empty_cell_label'] : __('We couldn\'t find any records.'));
        $this->set_count_sub_totals(isset($data['count_subtotals']) ? (bool) $data['count_subtotals'] : false);
        $this->_sub_totals = $subtotals;
        $this->set_count_totals(isset($data['count_totals']) ? (bool) $data['count_totals'] : false);
        $this->_totals = $totals;
    }
    /**
     * Retrieve the list of columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = $this->get_layout()->get_child_blocks($this->get_name_in_layout());
        foreach ($columns as $key => $column) {
            if (!$column->is_displayed()) {
                unset($columns[$key]);
            }
        }
        return $columns;
    }
    /**
     * Count columns
     *
     * @return int
     */
    public function get_column_count()
    {
        return count($this->get_columns());
    }
    /**
     * Set sortability flag for columns
     *
     * @param bool $value
     * @return $this
     */
    public function set_sortable($value)
    {
        if ($value === false) {
            foreach ($this->get_columns() as $column) {
                $column->set_sortable(false);
            }
        }
        return $this;
    }
    /**
     * Set custom renderer type for columns
     *
     * @param string $type
     * @param string $className
     * @return $this
     */
    public function set_renderer_type($type, $class_name)
    {
        foreach ($this->get_columns() as $column) {
            $column->set_renderer_type($type, $class_name);
        }
        return $this;
    }
    /**
     * Set custom filter type for columns
     *
     * @param string $type
     * @param string $className
     * @return $this
     */
    public function set_filter_type($type, $class_name)
    {
        foreach ($this->get_columns() as $column) {
            $column->set_filter_type($type, $class_name);
        }
        return $this;
    }
    /**
     * Prepare block for rendering
     *
     * @return void
     */
    protected function _before_to_html()
    {
        $columns = $this->get_columns();
        foreach ($columns as $column_id => $column) {
            $column->set_id($column_id);
            $column->set_grid($this->get_grid());
            if ($column->is_grouped()) {
                $this->is_column_grouped($column->get_index(), true);
            }
        }
        $last = array_pop($columns);
        if ($last) {
            $last->add_header_css_class('last');
        }
    }
    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function get_row_url($item)
    {
        $url = '#';
        if (null !== $this->_row_url_generator) {
            $url = $this->_row_url_generator->get_url($item);
        }
        return $url;
    }
    /**
     * Get children of specified item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function get_multiple_rows($item)
    {
        $children = $item->get_children();
        return $children ?: [];
    }
    /**
     * Has children of specified item
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     */
    public function has_multiple_rows($item)
    {
        return $item->has_children() && count($item->get_children()) > 0;
    }
    /**
     * Retrieve columns for multiple rows
     * @return array
     */
    public function get_multiple_row_columns()
    {
        $columns = $this->get_columns();
        foreach ($this->_grouped_column as $column) {
            unset($columns[$column]);
        }
        return $columns;
    }
    /**
     * Check whether subtotal should be rendered
     *
     * @param \Magento\Framework\DataObject $item
     * @return boolean
     */
    public function should_render_sub_total($item)
    {
        return $this->get_count_sub_totals() && count($this->get_multiple_rows($item)) > 0;
    }
    /**
     * Check whether total should be rendered
     *
     * @return boolean
     */
    public function should_render_total()
    {
        return $this->get_count_totals() && count($this->get_collection()) > 0;
    }
    /**
     * Retrieve rowspan number
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return int|false
     */
    public function get_rowspan($item, $column)
    {
        if ($this->is_column_grouped($column)) {
            return count($this->get_multiple_rows($item)) + count($this->_grouped_column) - 1 + (int) $this->should_render_sub_total($item);
        }
        return false;
    }
    /**
     * Check whether given column is grouped
     *
     * @param string|object $column
     * @param string $value
     * @return bool|$this
     */
    public function is_column_grouped($column, $value = null)
    {
        if (null === $value) {
            if (is_object($column)) {
                return in_array($column->get_index(), $this->_grouped_column);
            }
            return in_array($column, $this->_grouped_column);
        }
        $this->_grouped_column[] = $column;
        return $this;
    }
    /**
     * Check whether should render empty cell
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return boolean
     */
    public function should_render_empty_cell($item, $column)
    {
        return $item->get_is_empty() && in_array($column['index'], $this->_grouped_column);
    }
    /**
     * Retrieve colspan for empty cell
     *
     * @return int
     */
    public function get_empty_cell_colspan()
    {
        return $this->get_column_count() - count($this->_grouped_column);
    }
    /**
     * Check whether should render cell
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return boolean
     */
    public function should_render_cell($item, $column)
    {
        if ($this->is_column_grouped($column) && $item->get_is_empty()) {
            return true;
        }
        if (!$item->get_is_empty()) {
            return true;
        }
        return false;
    }
    /**
     * Set visibility of column headers
     *
     * @param boolean $visible
     * @return void
     */
    public function set_headers_visibility($visible = true)
    {
        $this->_headers_visibility = $visible;
    }
    /**
     * Return visibility of column headers
     *
     * @return boolean
     */
    public function is_header_visible()
    {
        return $this->_headers_visibility;
    }
    /**
     * Set visibility of filter
     *
     * @param bool $visible
     * @return void
     */
    public function set_filter_visibility($visible = true)
    {
        $this->_filter_visibility = $visible;
    }
    /**
     * Return visibility of filter
     *
     * @return boolean
     */
    public function is_filter_visible()
    {
        return $this->_filter_visibility;
    }
    /**
     * Set empty text CSS class
     *
     * @param string $cssClass
     * @return $this
     */
    public function set_empty_text_class($css_class)
    {
        $this->_empty_text_css = $css_class;
        return $this;
    }
    /**
     * Return empty text CSS class
     *
     * @return string
     */
    public function get_empty_text_class()
    {
        return $this->_empty_text_css;
    }
    /**
     * Retrieve label for empty cell
     *
     * @return string
     */
    public function get_empty_cell_label()
    {
        return $this->_empty_cell_label;
    }
    /**
     * Set label for empty cell
     *
     * @param string $label
     * @return $this
     */
    public function set_empty_cell_label($label)
    {
        $this->_empty_cell_label = $label;
        return $this;
    }
    /**
     * Set flag whether is collapsed
     *
     * @param bool $isCollapsed
     * @return $this
     */
    public function set_is_collapsed($is_collapsed)
    {
        $this->_is_collapsed = $is_collapsed;
        return $this;
    }
    /**
     * Retrieve flag is collapsed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_collapsed()
    {
        return $this->_is_collapsed;
    }
    /**
     * Return grid of current column set
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    public function get_grid()
    {
        return $this->get_parent_block();
    }
    /**
     * Return collection of current grid
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function get_collection()
    {
        return $this->get_grid()->get_collection();
    }
    /**
     * Set subtotals
     *
     * @param bool $flag
     * @return $this
     */
    public function set_count_sub_totals($flag = true)
    {
        $this->_count_sub_totals = $flag;
        return $this;
    }
    /**
     * Return count subtotals
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_count_sub_totals()
    {
        return $this->_count_sub_totals;
    }
    /**
     * Set totals
     *
     * @param bool $flag
     * @return $this
     */
    public function set_count_totals($flag = true)
    {
        $this->_count_totals = $flag;
        return $this;
    }
    /**
     * Return count totals
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_count_totals()
    {
        return $this->_count_totals;
    }
    /**
     * Retrieve subtotal for item
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    public function get_sub_totals($item)
    {
        $this->_prepare_sub_totals();
        $this->_sub_totals->reset();
        return $this->_sub_totals->count_totals($item->get_children());
    }
    /**
     * Retrieve subtotal items
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_totals()
    {
        $this->_prepare_totals();
        $this->_totals->reset();
        return $this->_totals->count_totals($this->get_collection());
    }
    /**
     * Update item with first sub-item data
     *
     * @param \Magento\Framework\DataObject $item
     * @return void
     */
    public function update_item_by_first_multi_row(\Magento\Framework\Data_Object $item)
    {
        $multi_rows = $this->get_multiple_rows($item);
        if (is_object($multi_rows) && $multi_rows instanceof \Magento\Framework\Data\Collection) {
            /** @var $multiRows \Magento\Framework\Data\Collection */
            $item->add_data($multi_rows->get_first_item()->get_data());
        } elseif (is_array($multi_rows)) {
            $first_item = $multi_rows[0];
            $item->add_data($first_item);
        }
    }
    /**
     * Prepare sub-total object for counting sub-totals
     *
     * @return void
     */
    public function _prepare_sub_totals()
    {
        $columns = $this->_sub_totals->get_columns();
        if (empty($columns)) {
            foreach ($this->get_multiple_row_columns() as $column) {
                if ($column->get_total()) {
                    $this->_sub_totals->set_column($column->get_index(), $column->get_total());
                }
            }
        }
    }
    /**
     * Prepare total object for counting totals
     *
     * @return void
     */
    public function _prepare_totals()
    {
        $columns = $this->_totals->get_columns();
        if (empty($columns)) {
            foreach ($this->get_columns() as $column) {
                if ($column->get_total()) {
                    $this->_totals->set_column($column->get_index(), $column->get_total());
                }
            }
        }
    }
}