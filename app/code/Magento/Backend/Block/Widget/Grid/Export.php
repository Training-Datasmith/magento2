<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Framework\App\Filesystem\Directory_List;
/**
 * Class Export for exporting grid data as CSV file or MS Excel 2003 XML Document file
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 * @see MAGETWO-67718
 */
class Export extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Grid\Export_Interface
{
    /**
     * Grid export types
     *
     * @var  \Magento\Framework\DataObject[]
     */
    protected $_export_types = [];
    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_export_page_size = 1000;
    /**
     * Template file name
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/export.phtml';
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_collection_factory;
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Data\Collection_Factory $collection_factory, array $data = [])
    {
        $this->_collection_factory = $collection_factory;
        parent::__construct($context, $data);
    }
    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->has_data('exportTypes')) {
            foreach ($this->get_data('exportTypes') as $type) {
                if (!isset($type['urlPath']) || !isset($type['label'])) {
                    throw new \Magento\Framework\Exception\Localized_Exception(__('Invalid export type supplied for grid export block'));
                }
                $this->add_export_type($type['urlPath'], $type['label']);
            }
        }
        $this->_directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
    }
    /**
     * Retrieve grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Column[]
     */
    protected function _get_columns()
    {
        return $this->get_parent_block()->get_columns();
    }
    /**
     * Retrieve totals
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _get_totals()
    {
        return $this->get_parent_block()->get_column_set()->get_totals();
    }
    /**
     * Return count totals
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_count_totals()
    {
        return $this->get_parent_block()->get_column_set()->should_render_total();
    }
    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    protected function _get_collection()
    {
        return $this->get_parent_block()->get_collection();
    }
    /**
     * Retrieve grid export types
     *
     * @return  \Magento\Framework\DataObject[]|false
     */
    public function get_export_types()
    {
        return empty($this->_export_types) ? false : $this->_export_types;
    }
    /**
     * Retrieve grid id
     *
     * @return string
     */
    public function get_id()
    {
        return $this->get_parent_block()->get_id();
    }
    /**
     * Prepare export button
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->set_child('export_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Export'), 'onclick' => $this->get_parent_block()->get_js_object_name() . '.doExport()', 'class' => 'task']));
        return parent::_prepare_layout();
    }
    /**
     * Render export button
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
        foreach ($this->_get_columns() as $column) {
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
        $totals = $this->_get_totals();
        $row = [];
        foreach ($this->_get_columns() as $column) {
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
        /** @var $originalCollection \Magento\Framework\Data\Collection */
        $original_collection = $this->get_parent_block()->get_prepared_collection();
        $count = null;
        $page = 1;
        $l_page = null;
        $break = false;
        while ($break !== true) {
            $original_collection->clear();
            $original_collection->set_page_size($this->get_export_page_size());
            $original_collection->set_cur_page($page);
            $original_collection->load();
            if ($count === null) {
                $count = $original_collection->get_size();
                $l_page = $original_collection->get_last_page_number();
            }
            if ($l_page == $page) {
                $break = true;
            }
            $page++;
            $collection = $this->_get_row_collection($original_collection);
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
        foreach ($this->_get_columns() as $column) {
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
        $name = hash('sha256', microtime());
        $file = $this->_path . '/' . $name . '.csv';
        $this->_directory->create($this->_path);
        $stream = $this->_directory->open_file($file, 'w+');
        $stream->write_csv($this->_get_export_headers());
        $stream->lock();
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
        $collection = $this->_get_prepared_collection();
        $data = [];
        foreach ($this->_get_columns() as $column) {
            if (!$column->get_is_system()) {
                $data[] = '"' . $column->get_export_header() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";
        foreach ($collection as $item) {
            $data = [];
            foreach ($this->_get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $data[] = '"' . str_replace(['"', '\\'], ['""', '\\\\'], $column->get_row_field_export($item) ?: '') . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }
        if ($this->get_count_totals()) {
            $data = [];
            foreach ($this->_get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $data[] = '"' . str_replace(['"', '\\'], ['""', '\\\\'], $column->get_row_field_export($this->_get_totals()) ?: '') . '"';
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
        $collection = $this->_get_prepared_collection();
        $indexes = [];
        foreach ($this->_get_columns() as $column) {
            if (!$column->get_is_system()) {
                $indexes[] = $column->get_index();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<items>';
        foreach ($collection as $item) {
            $xml .= $item->to_xml($indexes);
        }
        if ($this->get_count_totals()) {
            $xml .= $this->_get_totals()->to_xml($indexes);
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
        foreach ($this->_get_columns() as $column) {
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
        $collection = $this->_get_prepared_collection();
        $convert = new \Magento\Framework\Convert\Excel($collection->getIterator(), [$this, 'getRowRecord']);
        $name = hash('sha256', microtime());
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
        $collection = $this->_get_prepared_collection();
        $headers = [];
        $data = [];
        foreach ($this->_get_columns() as $column) {
            if (!$column->get_is_system()) {
                $headers[] = $column->get_header();
            }
        }
        $data[] = $headers;
        foreach ($collection as $item) {
            $row = [];
            foreach ($this->_get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $row[] = $column->get_row_field($item);
                }
            }
            $data[] = $row;
        }
        if ($this->get_count_totals()) {
            $row = [];
            foreach ($this->_get_columns() as $column) {
                if (!$column->get_is_system()) {
                    $row[] = $column->get_row_field($this->_get_totals());
                }
            }
            $data[] = $row;
        }
        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        return $convert->convert('single_sheet');
    }
    /**
     * Reformat base collection into collection without sub-collection in items
     *
     * @param \Magento\Framework\Data\Collection $baseCollection
     * @return \Magento\Framework\Data\Collection
     */
    protected function _get_row_collection(?\Magento\Framework\Data\Collection $base_collection = null)
    {
        if (null === $base_collection) {
            $base_collection = $this->get_parent_block()->get_prepared_collection();
        }
        $collection = $this->_collection_factory->create();
        /** @var $item \Magento\Framework\DataObject */
        foreach ($base_collection as $item) {
            if ($item->get_is_empty()) {
                continue;
            }
            if ($item->has_children() && count($item->get_children()) > 0) {
                /** @var $subItem \Magento\Framework\DataObject */
                foreach ($item->get_children() as $sub_item) {
                    $tmp_item = clone $item;
                    $tmp_item->uns_children();
                    $tmp_item->add_data($sub_item->get_data());
                    $collection->add_item($tmp_item);
                }
            } else {
                $collection->add_item($item);
            }
        }
        return $collection;
    }
    /**
     * Return prepared collection as row collection with additional conditions
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function _get_prepared_collection()
    {
        /** @var $collection \Magento\Framework\Data\Collection */
        $collection = $this->get_parent_block()->get_prepared_collection();
        $collection->set_page_size(0);
        $collection->load();
        return $this->_get_row_collection($collection);
    }
    /**
     * Get export page size
     *
     * @return int
     */
    public function get_export_page_size()
    {
        return $this->_export_page_size;
    }
}