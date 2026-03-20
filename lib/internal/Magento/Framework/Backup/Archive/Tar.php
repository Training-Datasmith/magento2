<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Extended version of \Magento\Framework\Archive\Tar that supports filtering
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup\Archive;

use Magento\Framework\Backup\Filesystem\Iterator\Filter;
use Recursive_Directory_Iterator;
use Recursive_Iterator_Iterator;
/**
 * Class to work with tar archives
 */
class Tar extends \Magento\Framework\Archive\Tar
{
    /**
     * Filenames or filename parts that are used for filtering files
     *
     * @var array
     */
    protected $_skip_files = [];
    /**
     *  Method same as it's parent but filters files using \Magento\Framework\Backup\Filesystem\Iterator\Filter
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     *
     * @see \Magento\Framework\Archive\Tar::_createTar()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _create_tar($skip_root = false, $finalize = false)
    {
        $path = $this->_get_current_file();
        $filesystem_iterator = new Recursive_Iterator_Iterator(new Recursive_Directory_Iterator($path, Recursive_Directory_Iterator::FOLLOW_SYMLINKS), Recursive_Iterator_Iterator::SELF_FIRST);
        $iterator = new Filter($filesystem_iterator, $this->_skip_files);
        foreach ($iterator as $item) {
            // exclude symlinks to do not get duplicates after follow symlinks in RecursiveDirectoryIterator
            if ($item->is_link()) {
                continue;
            }
            $this->_set_current_file($item->get_pathname());
            $this->_pack_and_write_current_file();
        }
        if ($finalize) {
            $this->_get_writer()->write(str_repeat("\x00", self::TAR_BLOCK_SIZE * 12));
        }
    }
    /**
     * Set files that shouldn't be added to tarball
     *
     * @param array $skipFiles
     * @return $this
     */
    public function set_skip_files(array $skip_files)
    {
        $this->_skip_files = $skip_files;
        return $this;
    }
}