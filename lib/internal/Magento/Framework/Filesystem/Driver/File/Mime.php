<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Driver\File;

use Magento\Framework\Exception\File_System_Exception;
/**
 * Mime type resolver.
 */
class Mime
{
    /**
     * @var array
     */
    private $mime_types = [
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
     * List of mime types that can be defined by file extension.
     *
     * @var array
     */
    private $define_by_extension_list = ['txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'svg' => 'image/svg+xml'];
    /**
     * List of generic MIME types
     *
     * The file mime type should be detected by the file's extension if the native mime type is one of the listed below.
     *
     * @var array
     */
    private $generic_mime_types = ['application/x-empty', 'inode/x-empty', 'application/octet-stream'];
    /**
     * Get mime type of a file
     *
     * @param string $path Absolute file path
     * @return string
     * @throws FileSystemException
     */
    public function get_mime_type(string $path): string
    {
        if (!file_exists($path)) {
            throw new File_System_Exception(__("File '{$path}' doesn't exist"));
        }
        $result = null;
        $extension = $this->get_file_extension($path);
        if (function_exists('mime_content_type')) {
            $result = $this->get_native_mime_type($path);
        } else {
            $image_info = getimagesize($path);
            $result = $image_info['mime'];
        }
        if (null === $result && isset($this->mime_types[$extension])) {
            $result = $this->mime_types[$extension];
        } elseif (null === $result) {
            $result = 'application/octet-stream';
        }
        return $result;
    }
    /**
     * Get mime type by the native mime_content_type function.
     *
     * Search for extended mime type if mime_content_type() returned 'application/octet-stream' or 'text/plain'
     *
     * @param string $file
     * @return string
     */
    private function get_native_mime_type(string $file): string
    {
        $extension = $this->get_file_extension($file);
        $result = mime_content_type($file);
        if (isset($this->mime_types[$extension], $this->define_by_extension_list[$extension]) && (strpos($result, 'text/') === 0 || strpos($result, 'image/svg') === 0 || in_array($result, $this->generic_mime_types, true))) {
            $result = $this->mime_types[$extension];
        }
        return $result;
    }
    /**
     * Get file extension by file name.
     *
     * @param string $path
     * @return string
     */
    private function get_file_extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
}