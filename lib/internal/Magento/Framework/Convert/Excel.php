<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Convert;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem\File\Write_Interface;
/**
 * Convert the data to XML Excel
 */
class Excel
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * \ArrayIterator Object
     *
     * @var \Iterator|null
     */
    protected $_iterator = null;
    /**
     * Method Callback Array
     *
     * @var array
     */
    protected $_row_callback = [];
    /**
     * Grid Header Array
     *
     * @var array
     */
    protected $_data_header = [];
    /**
     * Grid Footer Array
     *
     * @var array
     */
    protected $_data_footer = [];
    /**
     * Class Constructor
     *
     * @param \Iterator $iterator
     * @param array $rowCallback
     * @param Escaper|null $escaper
     */
    public function __construct(\Iterator $iterator, $row_callback = [], ?Escaper $escaper = null)
    {
        $this->_iterator = $iterator;
        $this->_row_callback = $row_callback;
        $this->escaper = $escaper ?? Object_Manager::get_instance()->get(Escaper::class);
    }
    /**
     * Retrieve Excel XML Document Header XML Fragment
     *
     * Append data header if it is available
     *
     * @param string $sheetName
     * @return string
     */
    protected function _get_xml_header($sheet_name = '')
    {
        if (empty($sheet_name)) {
            $sheet_name = 'Sheet 1';
        }
        $sheet_name = $this->escaper->escape_html($sheet_name);
        $xml_header = '<' . '?xml version="1.0"?' . '><' . '?mso-application progid="Excel.Sheet"?' . '><Workbook' . ' xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . ' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"' . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . ' xmlns:o="urn:schemas-microsoft-com:office:office"' . ' xmlns:html="http://www.w3.org/TR/REC-html40"' . ' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">' . '<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">' . '</OfficeDocumentSettings>' . '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">' . '</ExcelWorkbook>' . '<Worksheet ss:Name="' . $sheet_name . '">' . '<Table>';
        if ($this->_data_header) {
            $xml_header .= $this->_get_xml_row($this->_data_header, false);
        }
        return $xml_header;
    }
    /**
     * Retrieve Excel XML Document Footer XML Fragment
     *
     * Append data footer if it is available
     *
     * @return string
     */
    protected function _get_xml_footer()
    {
        $xml_footer = '';
        if ($this->_data_footer) {
            $xml_footer = $this->_get_xml_row($this->_data_footer, false);
        }
        $xml_footer .= '</Table></Worksheet></Workbook>';
        return $xml_footer;
    }
    /**
     * Get a Single XML Row
     *
     * @param array $row
     * @param boolean $useCallback
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _get_xml_row($row, $use_callback)
    {
        if ($use_callback && $this->_row_callback) {
            $row = call_user_func($this->_row_callback, $row);
        }
        $xml_data = [];
        $xml_data[] = '<Row>';
        foreach ($row as $value) {
            $value = $this->escaper->escape_html($value);
            $data_type = is_numeric($value) && (is_string($value) && ctype_space($value[0]) === false) && $value[0] !== '+' && $value[0] !== '0' ? 'Number' : 'String';
            /**
             * Security enhancement for CSV data processing by Excel-like applications.
             * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
             *
             * @var $value string|\Magento\Framework\Phrase
             */
            if (!is_string($value)) {
                $value = (string) $value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $value = ' ' . $value;
                $data_type = 'String';
            }
            $value = str_replace("\r\n", '&#10;', $value);
            $value = str_replace("\r", '&#10;', $value);
            $value = str_replace("\n", '&#10;', $value);
            $xml_data[] = '<Cell><Data ss:Type="' . $data_type . '">' . $value . '</Data></Cell>';
        }
        $xml_data[] = '</Row>';
        return join('', $xml_data);
    }
    /**
     * Set Data Header
     *
     * @param array $data
     * @return void
     */
    public function set_data_header($data)
    {
        $this->_data_header = $data;
    }
    /**
     * Set Data Footer
     *
     * @param array $data
     * @return void
     */
    public function set_data_footer($data)
    {
        $this->_data_footer = $data;
    }
    /**
     * Convert Data to Excel XML Document
     *
     * @param string $sheetName
     * @return string
     */
    public function convert($sheet_name = '')
    {
        $xml = $this->_get_xml_header($sheet_name);
        foreach ($this->_iterator as $data_row) {
            $xml .= $this->_get_xml_row($data_row, true);
        }
        $xml .= $this->_get_xml_footer();
        return $xml;
    }
    /**
     * Write Converted XML Data to Temporary File
     *
     * @param WriteInterface $stream
     * @param string $sheetName
     * @return void
     */
    public function write(Write_Interface $stream, $sheet_name = '')
    {
        $stream->write($this->_get_xml_header($sheet_name));
        foreach ($this->_iterator as $data_row) {
            $stream->write($this->_get_xml_row($data_row, true));
        }
        $stream->write($this->_get_xml_footer());
    }
}