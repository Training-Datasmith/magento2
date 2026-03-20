<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Data;

/**
 * Data transfer object to store config data for config options
 * @api
 * @since 100.0.2
 */
class Config_Data
{
    /**
     * @var string
     */
    private $file_key;
    /**
     * @var array
     */
    private $data = [];
    /**
     * Override previous config options when save
     *
     * @var bool
     */
    private $override_when_save = false;
    /**
     * Constructor
     *
     * @param string $fileKey
     */
    public function __construct($file_key)
    {
        $this->file_key = $file_key;
    }
    /**
     * Gets File Key
     *
     * @return string
     */
    public function get_file_key()
    {
        return $this->file_key;
    }
    /**
     * Gets Data
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }
    /**
     * Sets override when save flag
     *
     * @param bool $overrideWhenSave
     * @return void
     * @since 100.0.5
     */
    public function set_override_when_save($override_when_save)
    {
        $this->override_when_save = $override_when_save;
    }
    /**
     * Gets override when save flag
     *
     * @return bool
     * @since 100.0.5
     */
    public function is_override_when_save()
    {
        return $this->override_when_save;
    }
    /**
     * Updates a value in ConfigData configuration by specified path
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function set($path, $value)
    {
        $chunks = $this->expand($path);
        $data = [];
        $element =& $data;
        while ($chunks) {
            $key = array_shift($chunks);
            if ($chunks) {
                $element[$key] = [];
                $element =& $element[$key];
            } else {
                $element[$key] = $value;
            }
        }
        $this->data = array_replace_recursive($this->data, $data);
    }
    /**
     * Expands a path into chunks
     *
     * All chunks must be not empty and there must be at least two.
     *
     * @param string $path
     * @return string[]
     * @throws \InvalidArgumentException
     */
    private function expand($path)
    {
        $chunks = explode('/', $path ?: '');
        foreach ($chunks as $chunk) {
            if ('' == $chunk) {
                throw new \InvalidArgumentException("Path '{$path}' is invalid. It cannot be empty nor start or end with '/'");
            }
        }
        return $chunks;
    }
}