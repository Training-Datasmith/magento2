<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Helper class that simplifies bz2 files stream reading and writing
 */
namespace Magento\Framework\Archive\Helper\File;

class Bz extends \Magento\Framework\Archive\Helper\File
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function _open($mode)
    {
        if (!extension_loaded('bz2')) {
            throw new \RuntimeException('PHP extension bz2 is required.');
        }
        $this->_file_handler = bzopen($this->_file_path, $mode);
        if (false === $this->_file_handler) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The "%1" file failed to open.', [$this->_file_path]));
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function _write($data)
    {
        $result = bzwrite($this->_file_handler, $data);
        if (false === $result) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The data failed to write to "%1".', [$this->_file_path]));
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function _read($length)
    {
        $data = bzread($this->_file_handler, $length);
        if (false === $data) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Failed to read data from %1', [$this->_file_path]));
        }
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    protected function _close()
    {
        bzclose($this->_file_handler);
    }
}