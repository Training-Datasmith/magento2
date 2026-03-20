<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write_Interface;
/**
 * Class for the handling of registration a new file for MBI.
 */
class File_Recorder
{
    /**
     * @var FileInfoFactory
     */
    private $file_info_factory;
    /**
     * Subdirectory path for an encoded file.
     */
    private string $file_subdirectory_path = 'analytics/';
    /**
     * File name of an encoded file.
     */
    private string $encoded_file_name = 'data.tgz';
    public function __construct(
        /**
         * Resource for managing FileInfo object.
         */
        private readonly File_Info_Manager $file_info_manager,
        File_Info_Factory $file_info_factory,
        private readonly Filesystem $filesystem
    )
    {
        $this->file_info_factory = $file_info_factory;
    }
    /**
     * Save new encrypted file, register it and remove old registered file.
     */
    public function record_new_file(Encoded_Context $encoded_context): bool
    {
        $directory = $this->filesystem->get_directory_write(Directory_List::MEDIA);
        $file_relative_path = $this->get_file_relative_path();
        $directory->write_file($file_relative_path, $encoded_context->get_content());
        $file_info = $this->file_info_manager->load();
        $this->register_file($encoded_context, $file_relative_path);
        $this->remove_old_file($file_info, $directory);
        return true;
    }
    /**
     * Return relative path to encoded file.
     */
    private function get_file_relative_path(): string
    {
        return $this->file_subdirectory_path . hash('sha256', time()) . '/' . $this->encoded_file_name;
    }
    /**
     * Register encoded file.
     *
     * @param string $fileRelativePath
     */
    private function register_file(Encoded_Context $encoded_context, $file_relative_path): bool
    {
        $new_file_info = $this->file_info_factory->create(['path' => $file_relative_path, 'initializationVector' => $encoded_context->get_initialization_vector()]);
        $this->file_info_manager->save($new_file_info);
        return true;
    }
    /**
     * Remove previously registered file.
     */
    private function remove_old_file(File_Info $file_info, Write_Interface $directory): bool
    {
        if (!$file_info->get_path()) {
            return true;
        }
        $directory->delete($file_info->get_path());
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $directory_name = dirname($file_info->get_path());
        if ($directory_name !== '.') {
            $directory->delete($directory_name);
        }
        return true;
    }
}