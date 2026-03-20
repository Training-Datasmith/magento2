<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Magento\Composer\Info_Command;
use Magento\Composer\Magento_Composer_Application;
use Magento\Composer\Require_Update_Dry_Run_Command;
use Magento\Framework\App\Filesystem\Directory_List;
class Magento_Composer_Application_Factory
{
    /**
     * @var string
     */
    private $path_to_composer_home;
    /**
     * @var string
     */
    private $path_to_composer_json;
    /**
     * Constructor
     *
     * @param ComposerJsonFinder $composerJsonFinder
     * @param DirectoryList $directoryList
     */
    public function __construct(Composer_Json_Finder $composer_json_finder, Directory_List $directory_list)
    {
        $this->path_to_composer_json = $composer_json_finder->find_composer_json();
        $this->path_to_composer_home = $directory_list->get_path(Directory_List::COMPOSER_HOME);
    }
    /**
     * Creates MagentoComposerApplication instance
     *
     * @return MagentoComposerApplication
     */
    public function create()
    {
        return new Magento_Composer_Application($this->path_to_composer_home, $this->path_to_composer_json);
    }
    /**
     * Creates InfoCommand instance
     *
     * @return InfoCommand
     */
    public function create_info_command()
    {
        return new Info_Command($this->create());
    }
    /**
     * Creates RequireUpdateDryRunCommand instance
     *
     * @return RequireUpdateDryRunCommand
     */
    public function create_require_update_dry_run_command()
    {
        return new Require_Update_Dry_Run_Command($this->create(), $this->create_info_command());
    }
}