<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Helper class that simplifies files stream reading and writing
 */
namespace Magento\Framework\Archive\Helper;

use Magento\Framework\Exception\Localized_Exception;
class File
{
    /**
     * Full path to directory where file located
     *
     * @var string
     */
    protected $_file_location;
    /**
     * File name
     *
     * @var string
     */
    protected $_file_name;
    /**
     * Full path (directory + filename) to file
     *
     * @var string
     */
    protected $_file_path;
    /**
     * File permissions that will be set if file opened in write mode
     *
     * @var int
     */
    protected $_chmod;
    /**
     * File handler
     *
     * @var resource
     */
    protected $_file_handler;
    /**
     * Whether file has been opened in write mode
     *
     * @var bool
     */
    protected $_is_in_write_mode;
    /**
     * Set file path via constructor
     *
     * @param string $filePath
     */
    public function __construct($file_path)
    {
        $path_info = pathinfo($file_path);
        $this->_file_path = $file_path;
        $this->_file_location = isset($path_info['dirname']) ? $path_info['dirname'] : '';
        $this->_file_name = isset($path_info['basename']) ? $path_info['basename'] : '';
    }
    /**
     * Close file if it's not closed before object destruction
     */
    public function __destruct()
    {
        if ($this->_file_handler) {
            $this->_close();
        }
    }
    /**
     * Open file
     *
     * @param string $mode
     * @param int $chmod
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function open($mode = 'w+', $chmod = null)
    {
        $this->_is_in_write_mode = $this->_is_writable_mode($mode);
        if ($this->_is_in_write_mode) {
            if (!is_writable($this->_file_location)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('You don\'t have permissions to write to the "%1" file.', [$this->_file_location]));
            }
            if (is_file($this->_file_path) && !is_writable($this->_file_path)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('You don\'t have the permissions to open the "%1" file for writing access.', [$this->_file_name]));
            }
        }
        if ($this->_is_readable_mode($mode) && (!is_file($this->_file_path) || !is_readable($this->_file_path))) {
            if (!is_file($this->_file_path)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" file doesn\'t exist. Verify the file and try again.', [$this->_file_path]));
            }
            if (!is_readable($this->_file_path)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('You don\'t have permissions to read the "%1" file.', [$this->_file_path]));
            }
        }
        $this->_open($mode);
        $this->_chmod = $chmod;
    }
    /**
     * Write data to file
     *
     * @param string $data
     * @return void
     */
    public function write($data)
    {
        $this->_check_file_opened();
        $this->_write($data);
    }
    /**
     * Read data from file
     *
     * @param int $length
     * @return string|boolean
     */
    public function read($length = 4096)
    {
        $data = false;
        $this->_check_file_opened();
        if ($length > 0) {
            $data = $this->_read($length);
        }
        return $data;
    }
    /**
     * Check whether end of file reached
     *
     * @return boolean
     */
    public function eof()
    {
        $this->_check_file_opened();
        return $this->_eof();
    }
    /**
     * Close file
     *
     * @return void
     */
    public function close()
    {
        $this->_check_file_opened();
        $this->_close();
        $this->_file_handler = false;
        if ($this->_is_in_write_mode && isset($this->_chmod)) {
            @chmod($this->_file_path, $this->_chmod);
        }
    }
    /**
     * Implementation of file opening
     *
     * @param string $mode
     * @return void
     * @throws LocalizedException
     */
    protected function _open($mode)
    {
        $this->_file_handler = @fopen($this->_file_path, $mode);
        if (false === $this->_file_handler) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" file failed to open.', [$this->_file_path]));
        }
    }
    /**
     * Implementation of writing data to file
     *
     * @param string $data
     * @return void
     * @throws LocalizedException
     */
    protected function _write($data)
    {
        $result = @fwrite($this->_file_handler, $data);
        if (false === $result) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The data failed to write to "%1".', [$this->_file_path]));
        }
    }
    /**
     * Implementation of file reading
     *
     * @param int $length
     * @return string
     * @throws LocalizedException
     */
    protected function _read($length)
    {
        $result = fread($this->_file_handler, $length);
        if (false === $result) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('Failed to read data from %1', [$this->_file_path]));
        }
        return $result;
    }
    /**
     * Implementation of EOF indicator
     *
     * @return boolean
     */
    protected function _eof()
    {
        return feof($this->_file_handler);
    }
    /**
     * Implementation of file closing
     *
     * @return void
     */
    protected function _close()
    {
        fclose($this->_file_handler);
    }
    /**
     * Check whether requested mode is writable mode
     *
     * @param string $mode
     * @return int
     */
    protected function _is_writable_mode($mode)
    {
        return preg_match('/(^[waxc])|(\+$)/', $mode);
    }
    /**
     * Check whether requested mode is readable mode
     *
     * @param string $mode
     * @return bool
     */
    protected function _is_readable_mode($mode)
    {
        return !$this->_is_writable_mode($mode);
    }
    /**
     * Check whether file is opened
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _check_file_opened()
    {
        if (!$this->_file_handler) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('File not opened'));
        }
    }
}