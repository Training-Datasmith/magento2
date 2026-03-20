<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Helper class that simplifies gz files stream reading and writing
 */
namespace Magento\Framework\Archive\Helper\File;

class Gz extends \Magento\Framework\Archive\Helper\File
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function _open($mode)
    {
        if (!extension_loaded('zlib')) {
            throw new \RuntimeException('PHP extension zlib is required.');
        }
        $this->_file_handler = gzopen($this->_file_path, $mode);
        if (false === $this->_file_handler) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The "%1" file failed to open.', [$this->_file_path]));
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function _write($data)
    {
        $result = gzwrite($this->_file_handler, $data);
        if (empty($result) && !empty($data)) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The data failed to write to "%1".', [$this->_file_path]));
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function _read($length)
    {
        return gzread($this->_file_handler, $length);
    }
    /**
     * {@inheritdoc}
     */
    protected function _eof()
    {
        return gzeof($this->_file_handler);
    }
    /**
     * {@inheritdoc}
     */
    protected function _close()
    {
        gzclose($this->_file_handler);
    }
}