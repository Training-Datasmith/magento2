<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File;

use Magento\Framework\Filesystem\Driver\File;
/**
 * Csv parse
 *
 * @api
 */
class Csv
{
    /**
     * @var int
     */
    protected $_line_length = 0;
    /**
     * @var string
     */
    protected $_delimiter = ',';
    /**
     * @var string
     */
    protected $_enclosure = '"';
    /**
     * @var string
     */
    private $escape = "\x00";
    /**
     * @var File
     */
    protected $file;
    /**
     * Constructor
     *
     * @param File $file File Driver used for writing CSV
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    /**
     * Set max file line length
     *
     * @param   int $length
     * @return  \Magento\Framework\File\Csv
     */
    public function set_line_length($length)
    {
        $this->_line_length = $length;
        return $this;
    }
    /**
     * Set CSV column delimiter
     *
     * @param   string $delimiter
     * @return  \Magento\Framework\File\Csv
     */
    public function set_delimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
        return $this;
    }
    /**
     * Set CSV column value enclosure
     *
     * @param   string $enclosure
     * @return  \Magento\Framework\File\Csv
     */
    public function set_enclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
        return $this;
    }
    /**
     * Retrieve CSV file data as array
     *
     * @param   string $file
     * @return  array
     * @throws \Exception
     */
    public function get_data($file)
    {
        $data = [];
        if (!file_exists($file)) {
            throw new \Exception('File "' . $file . '" does not exist');
        }
        $fh = fopen($file, 'r');
        while ($row_data = fgetcsv($fh, $this->_line_length, $this->_delimiter, $this->_enclosure, $this->escape)) {
            $data[] = $row_data;
        }
        fclose($fh);
        return $data;
    }
    /**
     * Retrieve CSV file data as pairs
     *
     * @param   string $file
     * @param   int $keyIndex
     * @param   int $valueIndex
     * @return  array
     */
    public function get_data_pairs($file, $key_index = 0, $value_index = 1)
    {
        $data = [];
        $csv_data = $this->get_data($file);
        foreach ($csv_data as $row_data) {
            if (isset($row_data[$key_index])) {
                $data[$row_data[$key_index]] = isset($row_data[$value_index]) ? $row_data[$value_index] : null;
            }
        }
        return $data;
    }
    /**
     * Saving data row array into file
     *
     * @param string $file
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @deprecated 102.0.0
     * @see appendData
     */
    public function save_data($file, $data)
    {
        return $this->append_data($file, $data, 'w');
    }
    /**
     * Replace the saveData method by allowing to select the input mode
     *
     * @param string $file
     * @param array $data
     * @param string $mode
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function append_data($file, $data, $mode = 'w')
    {
        $file_handler = fopen($file, $mode);
        foreach ($data as $data_row) {
            $this->file->file_put_csv($file_handler, $data_row, $this->_delimiter, $this->_enclosure);
        }
        fclose($file_handler);
        return $this;
    }
}