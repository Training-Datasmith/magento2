<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Response\Http;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Response_Interface;
use Magento\Framework\Filesystem;
/**
 * Class FileFactory serves to declare file content in response for download.
 *
 * @api
 */
class File_Factory
{
    /**
     * @deprecated
     * @see $fileResponseFactory
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Framework\App\Response\FileFactory
     */
    private $file_response_factory;
    /**
     * @param ResponseInterface $response
     * @param Filesystem $filesystem
     * @param \Magento\Framework\App\Response\FileFactory|null $fileResponseFactory
     */
    public function __construct(\Magento\Framework\App\Response_Interface $response, \Magento\Framework\Filesystem $filesystem, ?\Magento\Framework\App\Response\File_Factory $file_response_factory = null)
    {
        $this->_response = $response;
        $this->_filesystem = $filesystem;
        $this->file_response_factory = $file_response_factory ?? Object_Manager::get_instance()->get(\Magento\Framework\App\Response\File_Factory::class);
    }
    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     *                              that case
     * @param string $baseDir
     * @param string $contentType
     * @param int $contentLength explicit content length, if strlen($content) isn't applicable
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\ResponseInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create($file_name, $content, $base_dir = Directory_List::ROOT, $content_type = 'application/octet-stream', $content_length = null)
    {
        $dir = $this->_filesystem->get_directory_write($base_dir);
        $is_file = false;
        $file = null;
        $file_content = $this->get_file_content($content);
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
            }
            if ($content['type'] == 'filename') {
                $is_file = true;
                $file = $content['value'];
                if (!$dir->is_file($file)) {
                    // phpcs:ignore Magento2.Exceptions.DirectThrow
                    throw new \Exception((string) new \Magento\Framework\Phrase('File not found'));
                }
                $content_length = $dir->stat($file)['size'];
            }
        }
        if ($content !== null) {
            if (!$is_file) {
                $dir->write_file($file_name, $file_content);
                $file = $file_name;
            }
        }
        return $this->file_response_factory->create(['options' => ['filePath' => $file, 'fileName' => $file_name, 'contentType' => $content_type, 'contentLength' => $content_length, 'directoryCode' => $base_dir, 'remove' => is_array($content) && !empty($content['rm'])]]);
    }
    /**
     * Returns file content for writing.
     *
     * @param string|array $content
     * @return string|array
     */
    private function get_file_content($content)
    {
        if (isset($content['type']) && $content['type'] === 'string') {
            return $content['value'];
        }
        return $content;
    }
}