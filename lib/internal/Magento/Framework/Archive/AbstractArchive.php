<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Class to work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

class Abstract_Archive
{
    /**
     * Write data to file. If file can't be opened - throw exception
     *
     * @param string $destination
     * @param string $data
     * @return true
     * @throws \Exception
     */
    protected function _write_file($destination, $data)
    {
        $destination = $destination !== null ? trim($destination) : '';
        if (false === file_put_contents($destination, $data)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Can't write to file: " . $destination);
        }
        return true;
    }
    /**
     * Read data from file. If file can't be opened, throw to exception.
     *
     * @param string $source
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _read_file($source)
    {
        $data = '';
        if (is_file($source) && is_readable($source)) {
            $data = @file_get_contents($source);
            if ($data === false) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase("Can't get contents from: %1", [$source]));
            }
        }
        return $data;
    }
    /**
     * Get file name from source (URI) without last extension.
     *
     * @param string $source
     * @param bool $withExtension
     * @return string
     */
    public function get_filename($source, $with_extension = false)
    {
        $file = $source !== null ? str_replace(dirname($source) . '/', '', $source) : '';
        if (!$with_extension) {
            $file = substr($file, 0, strrpos($file, '.'));
        }
        return $file;
    }
}