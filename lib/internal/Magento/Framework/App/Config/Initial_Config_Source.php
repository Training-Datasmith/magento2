<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Deployment_Config\Reader;
use Magento\Framework\Data_Object;
/**
 * Responsible for reading sources from files: config.dist.php, config.local.php, config.php
 */
class Initial_Config_Source implements Config_Source_Interface
{
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var string
     */
    private $config_type;
    /**
     * @var string
     * @deprecated 101.0.0 Initial configs can not be separated since 2.2.0 version
     */
    private $file_key;
    /**
     * DataProvider constructor.
     *
     * @param Reader $reader
     * @param string $configType
     * @param string $fileKey
     */
    public function __construct(Reader $reader, $config_type, $file_key = null)
    {
        $this->reader = $reader;
        $this->config_type = $config_type;
        $this->file_key = $file_key;
    }
    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $data = new Data_Object($this->reader->load());
        if ($path !== '' && $path !== null) {
            $path = '/' . $path;
        }
        return $data->get_data($this->config_type . $path) ?: [];
    }
}