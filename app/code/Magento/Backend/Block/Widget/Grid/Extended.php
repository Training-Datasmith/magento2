<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Framework\App\Filesystem\Directory_List;
/**
 * Extended Grid Widget
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 * @see \Magento\Backend\Block\Widget\Grid
 */
class Extended extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Grid\Export_Interface
{
    /**
     * Columns array
     *
     * array(
     *      'header'    => string,
     *      'width'     => int,
     *      'sortable'  => bool,
     *      'index'     => string,
     *      //'renderer'  => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Interface,
     *      'format'    => string
     *      'total'     => string (sum, avg)
     * )
     * @var array
     */
    protected $_columns = [];
    /**
     * Collection object
     *
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;
    /**
     * Export flag
     *
     * @var bool
     */
    protected $_is_export = false;
    /**
     * Grid export types
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_export_types = [];
    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_export_page_size = 1000;
    /**
     * Identifier of last grid column
     *
     * @var string
     */
    protected $_last_column_id;
    /**
     * Massaction row id field
     *
     * @var string
     */
    protected $_massaction_id_field;
    /**
     * Massaction row id filter
     *
     * @var string
     */
    protected $_massaction_id_filter;
    /**
     * @var string
     */
    protected $_massaction_block_name = \Magento\Backend\Block\Widget\Grid\Massaction\Extended::class;
    /**
     * Columns view order
     *
     * @var array
     */
    protected $_columns_order = [];
    /**
     * Label for empty cell
     *
     * @var string
     */
    protected $_empty_cell_label = '';
    /**
     * Columns to group by
     *
     * @var string[]
     */
    protected $_grouped_column = [];
    /**
     * Column headers visibility
     *
     * @var boolean
     */
    protected $_headers_visibility = true;
    /**
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
     * @var string|null
     */
    protected $_empty_text_css = 'empty-text';
    /**
     * @var bool
     */
    protected $_is_collapsed;
    /**
     * @var boolean
     */
    protected $_count_sub_totals = false;
    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_subtotals = [];
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/extended.phtml';
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_directory;
    /**
     * Additional path to folder
     *
     * @var string
     */
    protected $_path = 'export';
    /**
     * Initialization
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_empty_text = __('We couldn\'t find any records.');
        $this->_directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
    }
    /**
     * Initialize child blocks
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->set_child('export_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Export'), 'onclick' => $this->get_js_object_name() . '.doExport()', 'class' => 'task']));
        $this->set_child('reset_filter_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Reset Filter'), 'onclick' => $this->get_js_object_name() . '.resetFilter()', 'class' => 'action-reset action-tertiary'])->set_data_attribute(['action' => 'grid-filter-reset']));
        $this->set_child('search_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Search'), 'onclick' => $this->get_js_object_name() . '.doFilter()', 'class' => 'task action-secondary'])->set_data_attribute(['action' => 'grid-filter-apply']));
        return parent::_prepare_layout();
    }
    /**
     * Retrieve column set block
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function get_column_set()
    {
        if (!$this->get_child_block('grid.columnSet')) {
            $this->set_child('grid.columnSet', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Grid\Column_Set::class));
        }
        return parent::get_column_set();
    }
    /**
     * Generate export button
     *
     * @return string
     */
    public function get_export_button_html()
    {
        return $this->get_child_html('export_button');
    }
    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  $this
     */
    public function add_export_type($url, $label)
    {
        $this->_export_types[] = new \Magento\Framework\Data_Object(['url' => $this->get_url($url, ['_current' => true]), 'label' => $label]);
        return $this;
    }
    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @return  $this
     * @throws  \Exception
     */
    public function add_column($column_id, $column)
    {
        if (is_array($column)) {
            $this->get_column_set()->set_child($column_id, $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Grid\Column\Extended::class)->set_data($column)->set_id($column_id)->set_grid($this));
            $this->get_column_set()->get_child_block($column_id)->set_grid($this);
        } else {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(__('Please correct the column format and try again.'));
        }
        $this->_last_column_id = $column_id;
        return $this;
    }
    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return $this
     */
    public function remove_column($column_id)
    {
        if ($this->get_column_set()->get_child_block($column_id)) {
            $this->get_column_set()->unset_child($column_id);
            if ($this->_last_column_id == $column_id) {
                $names = $this->get_column_set()->get_child_names();
                $this->_last_column_id = array_pop($names);
            }
        }
        return $this;
    }
    /**
     * Add column to grid after specified column.
     *
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @param   string $after
     * @return  $this
     */
    public function add_column_after($column_id, $column, $after)
    {
        $this->add_column($column_id, $column);
        $this->add_columns_order($column_id, $after);
        return $this;
    }
    /**
     * Add column view order
     *
     * @param string $columnId
     * @param string $after
     * @return $this
     */
    public function add_columns_order($column_id, $after)
    {
        $this->_columns_order[$column_id] = $after;
        return $this;
    }
    /**
     * Retrieve columns order
     *
     * @return array
     */
    public function get_columns_order()
    {
        return $this->_columns_order;
    }
    /**
     * Sort columns by predefined order
     *
     * @return $this
     */
    public function sort_columns_by_order()
    {
        foreach ($this->get_columns_order() as $column_id => $after) {
            $this->get_layout()->reorder_child($this->get_column_set()->get_name_in_layout(), $this->get_column($column_id)->get_name_in_layout(), $this->get_column($after)->get_name_in_layout());
        }
        $columns = $this->get_column_set()->get_child_names();
        $this->_last_column_id = array_pop($columns);
        return $this;
    }
    /**
     * Retrieve identifier of last column
     *
     * @return string
     */
    public function get_last_column_id()
    {
        return $this->_last_column_id;
    }
    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepare_columns()
    {
        $this->sort_columns_by_order();
        return $this;
    }
    /**
     * Prepare grid massaction block
     *
     * @return $this
     */
    protected function _prepare_massaction_block()
    {
        $this->set_child('massaction', $this->get_layout()->create_block($this->get_massaction_block_name()));
        $this->_prepare_massaction();
        if ($this->get_massaction_block()->is_available()) {
            $this->_prepare_massaction_column();
        }
        return $this;
    }
    /**
     * Prepare grid massaction actions
     *
     * @return $this
     */
    protected function _prepare_massaction()
    {
        return $this;
    }
    /**
     * Prepare grid massaction column
     *
     * @return $this
     */
    protected function _prepare_massaction_column()
    {
        $column_id = 'massaction';
        $massaction_column = $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Grid\Column::class)->set_data(['index' => $this->get_massaction_id_field(), 'filter_index' => $this->get_massaction_id_filter(), 'type' => 'massaction', 'name' => $this->get_massaction_block()->get_form_field_name(), 'is_system' => true, 'header_css_class' => 'col-select', 'column_css_class' => 'col-select']);
        if ($this->get_no_filter_massaction_column()) {
            $massaction_column->set_data('filter', false);
        }
        $massaction_column->set_selected($this->get_massaction_block()->get_selected())->set_grid($this)->set_id($column_id);
        $this->get_column_set()->insert($massaction_column, count($this->get_column_set()->get_columns()) + 1, false, $column_id);
        return $this;
    }
    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepare_collection()
    {
        if ($this->get_collection()) {
            parent::_prepare_collection();
            if (!$this->_is_export) {
                $this->get_collection()->load();
                $this->_after_load_collection();
            }
        }
        return $this;
    }
    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _after_load_collection()
    {
        return $this;
    }
    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _prepare_grid()
    {
        $this->_prepare_columns();
        $this->_prepare_massaction_block();
        parent::_prepare_grid();
        return $this;
    }
    /**
     * Retrieve grid HTML
     *
     * @return string
     */
    public function get_html()
    {
        return $this->to_html();
    }
    /**
     * Retrieve massaction row identifier field
     *
     * @return string
     */
    public function get_massaction_id_field()
    {
        return $this->_massaction_id_field;
    }
    /**
     * Set massaction row identifier field
     *
     * @param  string    $idField
     * @return $this
     */
    public function set_massaction_id_field($id_field)
    {
        $this->_massaction_id_field = $id_field;
        return $this;
    }
    /**
     * Retrieve massaction row identifier filter
     *
     * @return string
     */
    public function get_massaction_id_filter()
    {
        return $this->_massaction_id_filter;
    }
    /**
     * Set massaction row identifier filter
     *
     * @param string $idFilter
     * @return $this
     */
    public function set_massaction_id_filter($id_filter)
    {
        $this->_massaction_id_filter = $id_filter;
        return $this;
    }
    /**
     * Retrieve massaction block name
     *
     * @return string
     */
    public function get_massaction_block_name()
    {
        return $this->_massaction_block_name;
    }
    /**
     * Set massaction block name
     *
     * @param  string    $blockName
     * @return $this
     */
    public function set_massaction_block_name($block_name)
    {
        $this->_massaction_block_name = $block_name;
        return $this;
    }
    /**
     * Retrieve massaction block
     *
     * @return $this
     */
    public function get_massaction_block()
    {
        return $this->get_child_block('massaction');
    }
    /**
     * Generate massaction block
     *
     * @return string
     */
    public function get_massaction_block_html()
    {
        return $this->get_child_html('massaction');
    }
    /**
     * Retrieve columns to render
     *
     * @return array
     */
    public function get_sub_total_columns()
    {
        return $this->get_columns();
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
     * Return row url for js event handlers
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function get_row_url($item)
    {
        // phpstan:ignore "Call to an undefined static method"
        $res = parent::get_row_url($item);
        return $res ? $res : '#';
    }
    /**
     * Get children of specified item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function get_multiple_rows($item)
    {
        return $item->get_children();
    }
    /**
     * Retrieve columns for multiple rows
     *
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
        return $this->_count_sub_totals && count($this->_subtotals) > 0 && count($this->get_multiple_rows($item)) > 0;
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
            return count($this->get_multiple_rows($item)) + count($this->_grouped_column);
        }
        return false;
    }
    /**
     * Check whether given column is grouped
     *
     * @param string|object $column
     * @param string $value
     * @return boolean|$this
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
     * Retrieve subtotal item
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject|string
     */
    public function get_sub_total_item($item)
    {
        foreach ($this->_subtotals as $subtotal_item) {
            foreach ($this->_grouped_column as $grouped_column) {
                if ($subtotal_item->get_data($grouped_column) == $item->get_data($grouped_column)) {
                    return $subtotal_item;
                }
            }
        }
        return '';
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
     * Set visibility of column headers
     *
     * @param bool $visible
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_headers_visibility()
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_filter_visibility()
    {
        return $this->_filter_visibility;
    }
    /**
     * Set empty text for grid
     *
     * @param string $text
     * @return $this
     */
    public function set_empty_text($text)
    {
        $this->_empty_text = $text;
        return $this;
    }
    /**
     * Return empty text for grid
     *
     * @return string
     */
    public function get_empty_text()
    {
        return $this->_empty_text;
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
     * Retrieve file content from file container array
     *
     * @param array $fileData
     * @return string
     */
    protected function _get_file_container_content(array $file_data)
    {
        return $this->_directory->read_file('export/' . $file_data['value']);
    }
    /**
     * Retrieve Headers row array for Export
     *
     * @return string[]
     */
    protected function _get_export_headers()
    {
        $row = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $row[] = $column->get_export_header();
            }
        }
        return $row;
    }
    /**
     * Retrieve Totals row array for Export
     *
     * @return string[]
     */
    protected function _get_export_totals()
    {
        $totals = $this->get_totals();
        $row = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $row[] = $column->has_totals_label() ? $column->get_totals_label() : $column->get_row_field_export($totals);
            }
        }
        return $row;
    }
    /**
     * Iterate collection and call callback method per item
     *
     * For callback method first argument always is item object
     *
     * @param string $callback
     * @param array $args additional arguments for callback method
     * @return void
     */
    public function _export_iterate_collection($callback, array $args)
    {
        $original_collection = $this->get_collection();
        $count = null;
        $page = 1;
        $l_page = null;
        $break = false;
        while ($break !== true) {
            $collection = clone $original_collection;
            $collection->set_page_size($this->_export_page_size);
            $collection->set_cur_page($page);
            $collection->load();
            if ($count === null) {
                $count = $collection->get_size();
                $l_page = $collection->get_last_page_number();
            }
            if ($l_page == $page) {
                $break = true;
            }
            $page++;
            foreach ($collection as $item) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                call_user_func_array(
                    [$this, $callback],
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    array_merge([$item], $args)
                );
            }
        }
    }
    /**
     * Write item data to csv export file
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     * @return void
     */
    protected function _export_csv_item(\Magento\Framework\Data_Object $item, \Magento\Framework\Filesystem\File\Write_Interface $stream)
    {
        $row = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $row[] = $column->get_row_field_export($item);
            }
        }
        $stream->write_csv($row);
    }
    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function get_csv_file()
    {
        $this->_is_export = true;
        $this->_prepare_grid();
        // phpcs:ignore Magento2.Security.InsecureFunction
        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.csv';
        $this->_directory->create($this->_path);
        $stream = $this->_directory->open_file($file, 'w+');
        $stream->lock();
        $stream->write(pack('CCC', 0xef, 0xbb, 0xbf));
        $stream->write_csv($this->_get_export_headers());
        $this->_export_iterate_collection('_exportCsvItem', [$stream]);
        if ($this->get_count_totals()) {
            $stream->write_csv($this->_get_export_totals());
        }
        $stream->unlock();
        $stream->close();
        return ['type' => 'filename', 'value' => $file, 'rm' => true];
    }
    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_csv()
    {
        $csv = '';
        $this->_is_export = true;
        $this->_prepare_grid();
        $this->get_collection()->get_select()->limit();
        $this->get_collection()->set_page_size(0);
        $this->get_collection()->load();
        $this->_after_load_collection();
        $data = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $data[] = '"' . $column->get_export_header() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";
        foreach ($this->get_collection() as $item) {
            $data = [];
            foreach ($this->get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $export_field = (string) $column->get_row_field_export($item);
                    $data[] = '"' . str_replace(['"', '\\'], ['""', '\\\\'], $export_field ?: '') . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }
        if ($this->get_count_totals()) {
            $data = [];
            foreach ($this->get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $data[] = '"' . str_replace(['"', '\\'], ['""', '\\\\'], $column->get_row_field_export($this->get_totals()) ?: '') . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }
        return $csv;
    }
    /**
     * Retrieve data in xml
     *
     * @return string
     */
    public function get_xml()
    {
        $this->_is_export = true;
        $this->_prepare_grid();
        $this->get_collection()->get_select()->limit();
        $this->get_collection()->set_page_size(0);
        $this->get_collection()->load();
        $this->_after_load_collection();
        $indexes = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $indexes[] = $column->get_index();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<items>';
        foreach ($this->get_collection() as $item) {
            $xml .= $item->to_xml($indexes);
        }
        if ($this->get_count_totals()) {
            $xml .= $this->get_totals()->to_xml($indexes);
        }
        $xml .= '</items>';
        return $xml;
    }
    /**
     *  Get a row data of the particular columns
     *
     * @param \Magento\Framework\DataObject $data
     * @return string[]
     */
    public function get_row_record(\Magento\Framework\Data_Object $data)
    {
        $row = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $row[] = $column->get_row_field_export($data);
            }
        }
        return $row;
    }
    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     */
    public function get_excel_file($sheet_name = '')
    {
        $this->_is_export = true;
        $this->_prepare_grid();
        $this->get_collection()->set_page_size(0);
        $convert = new \Magento\Framework\Convert\Excel($this->get_collection()->getIterator(), [$this, 'getRowRecord']);
        // phpcs:ignore Magento2.Security.InsecureFunction
        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.xml';
        $this->_directory->create($this->_path);
        $stream = $this->_directory->open_file($file, 'w+');
        $stream->lock();
        $convert->set_data_header($this->_get_export_headers());
        if ($this->get_count_totals()) {
            $convert->set_data_footer($this->_get_export_totals());
        }
        $convert->write($stream, $sheet_name);
        $stream->unlock();
        $stream->close();
        return ['type' => 'filename', 'value' => $file, 'rm' => true];
    }
    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     */
    public function get_excel()
    {
        $this->_is_export = true;
        $this->_prepare_grid();
        $this->get_collection()->get_select()->limit();
        $this->get_collection()->set_page_size(0);
        $this->get_collection()->load();
        $this->_after_load_collection();
        $headers = [];
        $data = [];
        foreach ($this->get_columns() as $column) {
            if (!$column->get_is_system()) {
                $headers[] = $column->get_header();
            }
        }
        $data[] = $headers;
        foreach ($this->get_collection() as $item) {
            $row = [];
            foreach ($this->get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $row[] = $column->get_row_field($item);
                }
            }
            $data[] = $row;
        }
        if ($this->get_count_totals()) {
            $row = [];
            foreach ($this->get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $row[] = $column->get_row_field($this->get_totals());
                }
            }
            $data[] = $row;
        }
        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        return $convert->convert('single_sheet');
    }
    /**
     * Retrieve grid export types
     *
     * @return \Magento\Framework\DataObject[]|false
     */
    public function get_export_types()
    {
        return empty($this->_export_types) ? false : $this->_export_types;
    }
    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function set_collection($collection)
    {
        $this->_collection = $collection;
    }
    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function get_collection()
    {
        return $this->_collection;
    }
    /**
     * Set subtotals
     *
     * @param boolean $flag
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
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_count_sub_totals()
    {
        return $this->_count_sub_totals;
    }
    /**
     * Set subtotal items
     *
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     */
    public function set_sub_totals(array $items)
    {
        $this->_subtotals = $items;
        return $this;
    }
    /**
     * Retrieve subtotal items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function get_sub_totals()
    {
        return $this->_subtotals;
    }
    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function get_main_buttons_html()
    {
        $html = '';
        if ($this->get_filter_visibility()) {
            $html .= $this->get_search_button_html();
            $html .= $this->get_reset_filter_button_html();
        }
        return $html;
    }
}