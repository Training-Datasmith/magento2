<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Model;

use Magento\Analytics\Report_Xml\DB\Report_Validator;
use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\Filesystem\File\Write_Interface as FileWriteInterface;
/**
 * Writes reports in files in csv format
 */
class Report_Writer implements Report_Writer_Interface
{
    /**
     * File name for error reporting file in archive
     */
    private string $errors_file_name = 'errors.csv';
    /**
     * ReportWriter constructor.
     */
    public function __construct(private readonly Config_Interface $config, private readonly Report_Validator $report_validator, private readonly Provider_Factory $provider_factory)
    {
    }
    /**
     * @inheritdoc
     */
    public function write(Write_Interface $directory, $path): bool
    {
        $errors_list = [];
        foreach ($this->config->get() as $file) {
            $provider = reset($file['providers']);
            if (isset($provider['parameters']['name'])) {
                $error = $this->report_validator->validate($provider['parameters']['name']);
                if ($error) {
                    $errors_list[] = $error;
                    continue;
                }
            }
            $this->prepare_data($provider, $directory, $path);
        }
        if ($errors_list) {
            $error_stream = $directory->open_file($path . $this->errors_file_name, 'w+');
            foreach ($errors_list as $error) {
                $error_stream->lock();
                $error_stream->write_csv($error);
                $error_stream->unlock();
            }
            $error_stream->close();
        }
        return true;
    }
    /**
     * Prepare report data
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function prepare_data(array $provider, Write_Interface $directory, string $path): void
    {
        /** @var  $providerObject */
        $provider_object = $this->provider_factory->create($provider['class']);
        $file_name = $provider['parameters'] ? $provider['parameters']['name'] : $provider['name'];
        $file_full_path = $path . $file_name . '.csv';
        $stream = $directory->open_file($file_full_path, 'w+');
        $stream->lock();
        if ($provider_object instanceof \Magento\Analytics\Report_Xml\Batch_Report_Provider_Interface) {
            $write_headers = true;
            $file_data = $provider_object->get_batch_report(...array_values($provider['parameters']));
            do {
                $this->do_write($file_data, $stream, $write_headers);
                $write_headers = false;
                $file_data = $provider_object->get_batch_report(...array_values($provider['parameters']));
                $file_data->rewind();
            } while ($file_data->valid());
        } else {
            $file_data = $provider_object->get_report(...array_values($provider['parameters']));
            $this->do_write($file_data, $stream);
        }
        $stream->unlock();
        $stream->close();
    }
    /**
     * Write data to file
     */
    private function do_write(\Traversable $file_data, File_Write_Interface $stream, bool $write_headers = true): void
    {
        foreach ($file_data as $row) {
            if ($write_headers) {
                $headers = array_keys($row);
                $stream->write_csv($headers);
                $write_headers = false;
            }
            $stream->write_csv($this->prepare_row($row));
        }
    }
    /**
     * Replace wrong symbols in row
     *
     * Strip backslashes before double quotes so they will be properly escaped in the generated csv
     *
     * @see fputcsv()
     */
    private function prepare_row(array $row): array
    {
        return preg_replace('/\\\\+(?=\")/', '', $row);
    }
}