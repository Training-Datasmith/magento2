<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Class to work with bzip2 archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

class Bz extends \Magento\Framework\Archive\Abstract_Archive implements \Magento\Framework\Archive\Archive_Interface
{
    /**
     * Pack file by BZIP2 compressor.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function pack($source, $destination)
    {
        $file_reader = new \Magento\Framework\Archive\Helper\File($source);
        $file_reader->open('r');
        $archive_writer = new \Magento\Framework\Archive\Helper\File\Bz($destination);
        $archive_writer->open('w');
        while (!$file_reader->eof()) {
            $archive_writer->write($file_reader->read());
        }
        $file_reader->close();
        $archive_writer->close();
        return $destination;
    }
    /**
     * Unpack file by BZIP2 compressor.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination)
    {
        if (is_dir($destination)) {
            $file = $this->get_filename($source);
            $destination = $destination . $file;
        }
        $archive_reader = new \Magento\Framework\Archive\Helper\File\Bz($source);
        $archive_reader->open('r');
        $file_writer = new \Magento\Framework\Archive\Helper\File($destination);
        $file_writer->open('w');
        while (!$archive_reader->eof()) {
            $file_writer->write($archive_reader->read());
        }
        return $destination;
    }
}