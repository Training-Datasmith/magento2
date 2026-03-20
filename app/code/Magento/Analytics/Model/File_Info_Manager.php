<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Flag_Manager;
/**
 * Manage saving and loading FileInfo object.
 */
class File_Info_Manager
{
    /**
     * @var FileInfoFactory
     */
    private $file_info_factory;
    /**
     * Flag code for a stored FileInfo object.
     */
    private string $flag_code = 'analytics_file_info';
    /**
     * Parameters which have to be saved into encoded form.
     */
    private array $encoded_parameters = ['initializationVector'];
    public function __construct(private readonly Flag_Manager $flag_manager, File_Info_Factory $file_info_factory)
    {
        $this->file_info_factory = $file_info_factory;
    }
    /**
     * Save FileInfo object.
     *
     * @throws LocalizedException
     */
    public function save(File_Info $file_info): bool
    {
        $parameters = [];
        $parameters['initializationVector'] = $file_info->get_initialization_vector();
        $parameters['path'] = $file_info->get_path();
        $empty_parameters = array_diff($parameters, array_filter($parameters));
        if ($empty_parameters) {
            throw new Localized_Exception(__('These arguments can\'t be empty "%1"', implode(', ', array_keys($empty_parameters))));
        }
        foreach ($this->encoded_parameters as $encoded_parameter) {
            $parameters[$encoded_parameter] = $this->encode_value($parameters[$encoded_parameter]);
        }
        $this->flag_manager->save_flag($this->flag_code, $parameters);
        return true;
    }
    /**
     * Load FileInfo object.
     *
     * @return FileInfo
     */
    public function load()
    {
        $parameters = $this->flag_manager->get_flag_data($this->flag_code) ?: [];
        $encoded_parameters = array_intersect($this->encoded_parameters, array_keys($parameters));
        foreach ($encoded_parameters as $encoded_parameter) {
            $parameters[$encoded_parameter] = $this->decode_value($parameters[$encoded_parameter]);
        }
        return $this->file_info_factory->create($parameters);
    }
    /**
     * Encode value.
     */
    private function encode_value(string $value): string
    {
        return base64_encode($value);
    }
    /**
     * Decode value.
     *
     * @param string $value
     */
    private function decode_value($value): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode($value);
    }
}