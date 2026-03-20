<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Filesystem;

use Magento\Framework\Backup\Filesystem\Iterator\Filter;
use Recursive_Directory_Iterator;
use Recursive_Iterator_Iterator;
/**
 * Filesystem helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Helper
{
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are writable
     *
     * @const int
     */
    public const INFO_WRITABLE = 1;
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are readable
     *
     * @const int
     */
    public const INFO_READABLE = 2;
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Get directory size
     *
     * @const int
     */
    public const INFO_SIZE = 4;
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Combination of INFO_WRITABLE, INFO_READABLE, INFO_SIZE
     *
     * @const int
     */
    public const INFO_ALL = 7;
    /**
     * Recursively delete $path
     *
     * @param string $path
     * @param array $skipPaths
     * @param bool $removeRoot
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function rm($path, $skip_paths = [], $remove_root = false)
    {
        $filesystem_iterator = new Recursive_Iterator_Iterator(new Recursive_Directory_Iterator($path), Recursive_Iterator_Iterator::CHILD_FIRST);
        $iterator = new Filter($filesystem_iterator, $skip_paths);
        foreach ($iterator as $item) {
            $item->is_dir() ? @rmdir($item->__toString()) : @unlink($item->__toString());
        }
        if ($remove_root && is_dir($path)) {
            @rmdir($path);
        }
    }
    /**
     * Get information (readable, writable, size) about $path
     *
     * @param string $path
     * @param int $infoOptions
     * @param array $skipFiles
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_info($path, $info_options = self::INFO_ALL, $skip_files = [])
    {
        $info = [];
        if ($info_options & self::INFO_READABLE) {
            $info['readable'] = true;
            $info['readableMeta'] = [];
        }
        if ($info_options & self::INFO_WRITABLE) {
            $info['writable'] = true;
            $info['writableMeta'] = [];
        }
        if ($info_options & self::INFO_SIZE) {
            $info['size'] = 0;
        }
        $filesystem_iterator = new Recursive_Iterator_Iterator(new Recursive_Directory_Iterator($path), Recursive_Iterator_Iterator::CHILD_FIRST);
        $iterator = new Filter($filesystem_iterator, $skip_files);
        foreach ($iterator as $item) {
            if ($item->is_link()) {
                continue;
            }
            if ($info_options & self::INFO_WRITABLE && !$item->is_writable()) {
                $info['writable'] = false;
                $info['writableMeta'][] = $item->get_pathname();
            }
            if ($info_options & self::INFO_READABLE && !$item->is_readable()) {
                $info['readable'] = false;
                $info['readableMeta'][] = $item->get_pathname();
            }
            if ($info_options & self::INFO_SIZE && !$item->is_dir()) {
                $info['size'] += $item->get_size();
            }
        }
        return $info;
    }
}