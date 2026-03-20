<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem\File_Resolver;
/**
 * Utility class for populating an autoloader with application-specific information for PSR-0 and PSR-4 mappings
 * and include-path contents
 */
class Populator
{
    /**
     * @param AutoloaderInterface $autoloader
     * @param DirectoryList $dirList
     * @return void
     */
    public static function populate_mappings(Autoloader_Interface $autoloader, Directory_List $dir_list)
    {
        $generation_dir = $dir_list->get_path(Directory_List::GENERATED_CODE);
        $autoloader->add_psr4('Magento\\', [$generation_dir . '/Magento/'], true);
        /** Required for code generation to occur */
        File_Resolver::add_include_path($generation_dir);
        /** Required to autoload custom classes */
        $autoloader->add_psr0('', [$generation_dir]);
    }
}