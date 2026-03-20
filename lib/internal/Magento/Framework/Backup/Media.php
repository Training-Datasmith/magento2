<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work media folder and database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Media extends Snapshot
{
    /**
     * Implementation Rollback functionality for Media
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function rollback()
    {
        $this->_prepare_ignore_list();
        return parent::rollback();
    }
    /**
     * Implementation Create Backup functionality for Media
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function create()
    {
        $this->_prepare_ignore_list();
        return parent::create();
    }
    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     */
    public function get_type()
    {
        return 'media';
    }
    /**
     * Add all folders and files except media and db backup to ignore list
     *
     * @return $this
     */
    protected function _prepare_ignore_list()
    {
        $root_dir = $this->get_root_dir();
        $map = [$root_dir => ['var', 'pub'], $root_dir . '/pub' => ['media'], $root_dir . '/var' => [$this->get_db_backup_filename()]];
        foreach ($map as $path => $white_list) {
            foreach (new \Directory_Iterator($path) as $item) {
                $filename = $item->get_filename();
                if (!$item->is_dot() && !in_array($filename, $white_list)) {
                    $this->add_ignore_paths(str_replace('\\', '/', $item->get_pathname() !== null ? $item->get_pathname() : ''));
                }
            }
        }
        return $this;
    }
}