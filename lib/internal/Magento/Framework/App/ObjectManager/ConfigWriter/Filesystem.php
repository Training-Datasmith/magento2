<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Object_Manager\Config_Writer;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager\Config_Writer_Interface;
/**
 * @inheritdoc
 */
class Filesystem implements Config_Writer_Interface
{
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(Directory_List $directory_list)
    {
        $this->directory_list = $directory_list;
    }
    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write(string $key, array $config)
    {
        $this->initialize();
        $configuration = sprintf('<?php return %s;', var_export($config, true));
        file_put_contents($this->directory_list->get_path(Directory_List::GENERATED_METADATA) . '/' . $key . '.php', $configuration);
    }
    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists($this->directory_list->get_path(Directory_List::GENERATED_METADATA))) {
            mkdir($this->directory_list->get_path(Directory_List::GENERATED_METADATA));
        }
    }
}