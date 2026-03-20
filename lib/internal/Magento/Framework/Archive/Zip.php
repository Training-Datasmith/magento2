<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Archive;

/**
 * Zip compressed file archive.
 */
class Zip extends Abstract_Archive implements Archive_Interface
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct()
    {
        $type = 'Zip';
        if (!class_exists('\ZipArchive')) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('\'%1\' file extension is not supported', [$type]));
        }
    }
    /**
     * Pack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination)
    {
        $zip = new \Zip_Archive();
        $zip->open($destination, \Zip_Archive::CREATE);
        $zip->add_file($source);
        $zip->close();
        return $destination;
    }
    /**
     * Unpack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        $zip = new \Zip_Archive();
        if ($zip->open($source) === true) {
            $base_name = basename($destination);
            $filename = $this->get_filename_from_zip($zip, $base_name);
            if ($filename) {
                $zip->extract_to(dirname($destination), $filename);
            } else {
                $destination = '';
            }
            $zip->close();
        } else {
            $destination = '';
        }
        return $destination;
    }
    /**
     * Retrieve filename for import from zip archive.
     *
     * @param \ZipArchive $zip
     * @param string $baseName
     *
     * @return string
     */
    private function get_filename_from_zip(\Zip_Archive $zip, string $base_name): string
    {
        $index = 0;
        do {
            $zip->rename_index($index, $base_name);
            $filename = $zip->get_name_index($index);
            $index++;
        } while ($base_name !== $filename && $filename !== false);
        return $filename === $base_name ? $filename : '';
    }
}