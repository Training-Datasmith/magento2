<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\Directory_List;
/**
 * A class to find path to root Composer json file
 */
class Composer_Json_Finder
{
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(Directory_List $directory_list)
    {
        $this->directory_list = $directory_list;
    }
    /**
     * Find absolute path to root Composer json file
     *
     * @return string
     * @throws \Exception
     */
    public function find_composer_json()
    {
        $composer_json = $this->directory_list->get_path(Directory_List::ROOT) . '/composer.json';
        $composer_json = realpath($composer_json);
        if ($composer_json === false) {
            throw new \Exception('Composer file not found');
        }
        return $composer_json;
    }
}