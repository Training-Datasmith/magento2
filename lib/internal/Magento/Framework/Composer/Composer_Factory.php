<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Composer\IO\Buffer_Io;
use Magento\Framework\App\Filesystem\Directory_List;
class Composer_Factory
{
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * @var ComposerJsonFinder
     */
    private $composer_json_finder;
    /**
     * @param DirectoryList $directoryList
     * @param ComposerJsonFinder $composerJsonFinder
     */
    public function __construct(Directory_List $directory_list, Composer_Json_Finder $composer_json_finder)
    {
        $this->directory_list = $directory_list;
        $this->composer_json_finder = $composer_json_finder;
    }
    /**
     * Create \Composer\Composer
     *
     * @return \Composer\Composer
     * @throws \Exception
     */
    public function create()
    {
        putenv('COMPOSER_HOME=' . $this->directory_list->get_path(Directory_List::COMPOSER_HOME));
        return \Composer\Factory::create(new Buffer_Io(), $this->composer_json_finder->find_composer_json());
    }
}