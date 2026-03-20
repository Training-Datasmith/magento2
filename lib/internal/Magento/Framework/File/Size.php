<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Magento file size lib
 */
namespace Magento\Framework\File;

/**
 * @api
 * @since 100.0.2
 */
class Size
{
    /**
     * Data size converter
     *
     * @var \Magento\Framework\Convert\DataSize
     */
    private $data_size;
    /**
     * Maximum file size for MAX_FILE_SIZE attribute of a form
     *
     * @link http://www.php.net/manual/en/features.file-upload.post-method.php
     * @var integer
     */
    protected static $_max_file_size = -1;
    /**
     * Get post max size
     *
     * @return string
     */
    public function get_post_max_size()
    {
        return $this->_ini_get('post_max_size');
    }
    /**
     * Get upload max size
     *
     * @return string
     */
    public function get_upload_max_size()
    {
        return $this->_ini_get('upload_max_filesize');
    }
    /**
     * Get max file size in megabytes
     *
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function get_max_file_size_in_mb($precision = 0, $mode = \PHP_ROUND_HALF_DOWN)
    {
        return $this->get_file_size_in_mb($this->get_max_file_size(), $precision, $mode);
    }
    /**
     * Get file size in megabytes
     *
     * @param int $fileSize
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function get_file_size_in_mb($file_size, $precision = 0, $mode = \PHP_ROUND_HALF_DOWN)
    {
        return round($file_size / (1024 * 1024), $precision, $mode);
    }
    /**
     * Get the maximum file size of the a form in bytes
     *
     * @return integer
     */
    public function get_max_file_size()
    {
        if (self::$_max_file_size < 0) {
            $post_max_size = $this->get_data_size()->convert_size_to_bytes($this->get_post_max_size());
            $upload_max_size = $this->get_data_size()->convert_size_to_bytes($this->get_upload_max_size());
            $min = max($post_max_size, $upload_max_size);
            if ($post_max_size > 0) {
                $min = min($min, $post_max_size);
            }
            if ($upload_max_size > 0) {
                $min = min($min, $upload_max_size);
            }
            self::$_max_file_size = $min;
        }
        return self::$_max_file_size;
    }
    /**
     * Converts a ini setting to a integer value
     *
     * @deprecated 100.1.0 Please use \Magento\Framework\Convert\DataSize
     *
     * @param string $size
     * @return integer
     */
    public function convert_size_to_integer($size)
    {
        return $this->get_data_size()->convert_size_to_bytes($size);
    }
    /**
     * Gets the value of a configuration option
     *
     * @link http://php.net/manual/en/function.ini-get.php
     * @param string $param The configuration option name
     * @return string
     */
    protected function _ini_get($param)
    {
        return trim(ini_get($param));
    }
    /**
     * The getter function to get the new dependency for real application code
     *
     * @return \Magento\Framework\Convert\DataSize
     *
     * @deprecated 100.1.0
     */
    private function get_data_size()
    {
        if ($this->data_size === null) {
            $this->data_size = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Convert\Data_Size::class);
        }
        return $this->data_size;
    }
}