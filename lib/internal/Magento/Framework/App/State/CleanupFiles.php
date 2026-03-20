<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\State;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
/**
 * A service for cleaning up application state
 */
class Cleanup_Files
{
    /**
     * File system
     *
     * @var Filesystem
     */
    private $filesystem;
    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    /**
     * Clears all files that are subject of code generation
     *
     * @return string[]
     */
    public function clear_code_generated_files()
    {
        return array_merge($this->clear_code_generated_classes(), $this->clear_materialized_view_files());
    }
    /**
     * Clears code-generated classes
     *
     * @return string[]
     */
    public function clear_code_generated_classes()
    {
        return array_merge($this->empty_dir(Directory_List::GENERATED_CODE), $this->empty_dir(Directory_List::GENERATED_METADATA));
    }
    /**
     * Clears materialized static view files
     *
     * @return string[]
     */
    public function clear_materialized_view_files()
    {
        return array_merge($this->empty_dir(Directory_List::STATIC_VIEW), $this->empty_dir(Directory_List::VAR_DIR, Directory_List::TMP_MATERIALIZATION_DIR));
    }
    /**
     * Clears all files
     *
     * @return string[]
     */
    public function clear_all_files()
    {
        return array_merge($this->empty_dir(Directory_List::STATIC_VIEW), $this->empty_dir(Directory_List::VAR_DIR));
    }
    /**
     * Deletes contents of specified directory
     *
     * @param string $code
     * @param string|null $subPath
     * @return string[]
     */
    private function empty_dir($code, $sub_path = null)
    {
        $messages = [];
        $dir = $this->filesystem->get_directory_write($code);
        $dir_path = $dir->get_absolute_path();
        if (!$dir->is_exist()) {
            $messages[] = "The directory '{$dir_path}' doesn't exist - skipping cleanup";
            return $messages;
        }
        foreach ($dir->search('*', $sub_path) as $path) {
            if ($path !== '.' && $path !== '..') {
                $messages[] = $dir_path . $path;
                try {
                    $dir->delete($path);
                } catch (File_System_Exception $e) {
                    $messages[] = $e->get_message();
                }
            }
        }
        return $messages;
    }
}