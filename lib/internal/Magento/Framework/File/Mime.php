<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
/**
 * Utility for mime type retrieval
 *
 * @deprecated
 * @see Filesystem\ExtendedDriverInterface::getMetadata()
 */
class Mime
{
    /**
     * Mime types
     *
     * @var array
     *
     * @deprecated
     */
    protected $mime_types = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
    ];
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @param Filesystem|null $filesystem
     */
    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: Object_Manager::get_instance()->get(Filesystem::class);
    }
    /**
     * Get mime type of a file.
     *
     * @param string $file
     * @return string
     * @throws FileSystemException
     *
     * @deprecated
     */
    public function get_mime_type($file)
    {
        $driver = $this->filesystem->get_directory_write(Directory_List::ROOT, Filesystem\Driver_Pool::FILE)->get_driver();
        /**
         * Try with non-local driver.
         */
        if (!$driver->is_exists($file)) {
            $driver = $this->filesystem->get_directory_write(Directory_List::ROOT)->get_driver();
        }
        if (!$driver->is_exists($file)) {
            throw new File_System_Exception(__("File '{$file}' doesn't exist"));
        }
        if ($driver instanceof Filesystem\Extended_Driver_Interface) {
            return $driver->get_metadata($file)['mimetype'];
        }
        $mime = new Filesystem\Driver\File\Mime();
        return $mime->get_mime_type($file);
    }
}