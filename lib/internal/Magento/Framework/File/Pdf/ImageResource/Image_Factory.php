<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File\Pdf\Image_Resource;

use Exception;
use finfo;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read_Interface;
use Zend_Pdf_Exception;
class Image_Factory
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    /**
     * New zend image factory instance
     *
     * @param string $filename
     * @return \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function factory(string $filename)
    {
        $media_reader = $this->filesystem->get_directory_read(Directory_List::MEDIA);
        if (!$media_reader->is_file($filename)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Cannot create image resource. File not found.');
        }
        $temp_filename_from_bucket_or_disk = $this->create_temporary_file_and_put_content($media_reader, $filename);
        $temp_resource_file_path = $this->get_file_path_of_temporary_file($temp_filename_from_bucket_or_disk);
        $type_of_image = $this->get_type_of_image($temp_resource_file_path, $filename);
        $zend_pdf_image = $this->get_zend_pdf_image($type_of_image, $temp_resource_file_path);
        $this->remove_temorary_file($temp_filename_from_bucket_or_disk);
        return $zend_pdf_image;
    }
    /**
     * Create a temporary file and put content of the original file into it
     *
     * @param ReadInterface $mediaReader
     * @param string $filename
     * @return resource
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function create_temporary_file_and_put_content(Read_Interface $media_reader, string $filename)
    {
        $temp_filename_from_bucket_or_disk = tmpfile();
        if ($temp_filename_from_bucket_or_disk === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Cannot create temporary file');
        }
        fwrite($temp_filename_from_bucket_or_disk, $media_reader->read_file($filename));
        return $temp_filename_from_bucket_or_disk;
    }
    /**
     * Returns the path of the temporary file or nothing
     *
     * @param resource $tempFilenameFromBucketOrDisk
     * @return string
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function get_file_path_of_temporary_file($temp_filename_from_bucket_or_disk): string
    {
        try {
            return stream_get_meta_data($temp_filename_from_bucket_or_disk)['uri'];
        } catch (Exception $e) {
            return '';
        }
    }
    /**
     * Get mime-type in safe way except internal errors
     *
     * @param string $filepath
     * @param string $baseFileName
     * @return mixed|string
     * @throws \Zend_Pdf_Exception
     */
    protected function get_type_of_image(string $filepath, string $base_file_name)
    {
        if (class_exists('finfo', false) && !empty($filepath)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $classic_mime_type = $finfo->file($filepath);
        } elseif (function_exists('mime_content_type') && !empty($filepath)) {
            $classic_mime_type = mime_content_type($filepath);
        } else {
            $classic_mime_type = $this->fetch_fallback_mime_type($base_file_name);
        }
        if (!empty($classic_mime_type)) {
            return explode('/', $classic_mime_type)[1] ?? '';
        } else {
            return '';
        }
    }
    /**
     * Fall back fetching of mimetype by original base file name
     *
     * @param string $baseFileName
     * @return string
     * @throws \Zend_Pdf_Exception
     */
    protected function fetch_fallback_mime_type(string $base_file_name): string
    {
        $extension = pathinfo($base_file_name, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'jpg':
            //Fall through to next case;
            case 'jpe':
            //Fall through to next case;
            case 'jpeg':
                $classic_mime_type = 'image/jpeg';
                break;
            case 'png':
                $classic_mime_type = 'image/png';
                break;
            case 'tif':
            //Fall through to next case;
            case 'tiff':
                $classic_mime_type = 'image/tiff';
                break;
            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Cannot create image resource. File extension not known or unsupported type.');
        }
        return $classic_mime_type;
    }
    /**
     * Creates instance of Zend_Pdf_Resource_Image
     *
     * @param string $typeOfImage
     * @param string $tempResourceFilePath
     * @return \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     */
    protected function get_zend_pdf_image(string $type_of_image, string $temp_resource_file_path)
    {
        $class_to_use_as_pdf_image = sprintf('Zend_Pdf_Resource_Image_%s', ucfirst($type_of_image));
        return new $class_to_use_as_pdf_image($temp_resource_file_path);
    }
    /**
     * Removes the temporary file from disk
     *
     * @param resource $tempFilenameFromBucketOrDisk
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function remove_temorary_file($temp_filename_from_bucket_or_disk): void
    {
        fclose($temp_filename_from_bucket_or_disk);
    }
}