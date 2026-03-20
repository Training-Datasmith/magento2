<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * Interface ExportInterface
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
interface Export_Interface
{
    /**
     * Retrieve grid export types
     *
     * @return array|bool
     */
    public function get_export_types();
    /**
     * Retrieve grid id
     *
     * @return string
     */
    public function get_id();
    /**
     * Render export button
     *
     * @return string
     */
    public function get_export_button_html();
    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  \Magento\Backend\Block\Widget\Grid
     */
    public function add_export_type($url, $label);
    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function get_csv_file();
    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function get_csv();
    /**
     * Retrieve data in xml
     *
     * @return string
     */
    public function get_xml();
    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     */
    public function get_excel_file($sheet_name = '');
    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     */
    public function get_excel();
}