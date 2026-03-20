<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Archive;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write_Interface;
/**
 * Class for the handling of a new data collection for MBI.
 */
class Export_Data_Handler implements Export_Data_Handler_Interface
{
    /**
     * Subdirectory path for all temporary files.
     */
    private string $subdirectory_path = 'analytics/';
    /**
     * Filename of archive with collected data.
     */
    private string $archive_name = 'data.tgz';
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Archive $archive,
        /**
         * Resource for write data of reports into separate files.
         */
        private readonly Report_Writer_Interface $report_writer,
        /**
         * Resource for encrypting data.
         */
        private readonly Cryptographer $cryptographer,
        /**
         * Resource for registration a new file.
         */
        private readonly File_Recorder $file_recorder
    )
    {
    }
    /**
     * @inheritdoc
     */
    public function prepare_export_data(): bool
    {
        try {
            $tmp_directory = $this->filesystem->get_directory_write(Directory_List::SYS_TMP);
            $this->prepare_directory($tmp_directory, $this->get_tmp_files_dir_relative_path());
            $this->report_writer->write($tmp_directory, $this->get_tmp_files_dir_relative_path());
            $tmp_files_directory_absolute_path = $this->validate_source($tmp_directory, $this->get_tmp_files_dir_relative_path());
            $archive_absolute_path = $this->prepare_file_directory($tmp_directory, $this->get_archive_relative_path());
            $this->pack($tmp_files_directory_absolute_path, $archive_absolute_path);
            $this->validate_source($tmp_directory, $this->get_archive_relative_path());
            $this->file_recorder->record_new_file($this->cryptographer->encode($tmp_directory->read_file($this->get_archive_relative_path())));
        } finally {
            if (isset($tmp_directory)) {
                $tmp_directory->delete($this->get_tmp_files_dir_relative_path());
                $tmp_directory->delete($this->get_archive_relative_path());
            }
        }
        return true;
    }
    /**
     * Return relative path to a directory for temporary files with reports data.
     */
    private function get_tmp_files_dir_relative_path(): string
    {
        return $this->subdirectory_path . 'tmp/' . $this->get_instance_identifier() . '/';
    }
    /**
     * Return unique identifier for an instance.
     */
    private function get_instance_identifier(): string
    {
        return hash('sha256', BP);
    }
    /**
     * Return relative path to a directory for an archive.
     */
    private function get_archive_relative_path(): string
    {
        return $this->subdirectory_path . $this->archive_name;
    }
    /**
     * Clean up a directory.
     *
     * @param string $path
     * @return string
     */
    private function prepare_directory(Write_Interface $directory, $path)
    {
        $directory->delete($path);
        return $directory->get_absolute_path($path);
    }
    /**
     * Remove a file and a create parent directory a file.
     *
     * @param string $path
     * @return string
     */
    private function prepare_file_directory(Write_Interface $directory, $path)
    {
        $directory->delete($path);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (dirname($path) !== '.') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $directory->create(dirname($path));
        }
        return $directory->get_absolute_path($path);
    }
    /**
     * Packing data into an archive.
     *
     * @param string $source
     * @param string $destination
     */
    private function pack($source, $destination): bool
    {
        $this->archive->pack(
            $source,
            $destination,
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            is_dir($source) ?: false
        );
        return true;
    }
    /**
     * Validate that data source exist.
     *
     * Return absolute path in a validated data source.
     *
     * @param string $path
     * @return string
     * @throws LocalizedException If source is not exist.
     */
    private function validate_source(Write_Interface $directory, $path)
    {
        if (!$directory->is_exist($path)) {
            throw new Localized_Exception(__('The "%1" source doesn\'t exist.', $directory->get_absolute_path($path)));
        }
        return $directory->get_absolute_path($path);
    }
}