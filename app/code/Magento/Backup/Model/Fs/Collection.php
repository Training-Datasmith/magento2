<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Model\Fs;

use Magento\Framework\App\Filesystem\Directory_List;
/**
 * Backup data collection
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_var_directory;
    /**
     * Folder, where all backups are stored
     *
     * @var string
     */
    protected $_path = 'backups';
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backup_data = null;
    /**
     * Backup model
     *
     * @var \Magento\Backup\Model\Backup
     */
    protected $_backup = null;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $_filesystem;
    /**
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backup\Model\Backup $backup
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(\Magento\Framework\Data\Collection\Entity_Factory $entity_factory, \Magento\Backup\Helper\Data $backup_data, \Magento\Framework\Filesystem $filesystem, \Magento\Backup\Model\Backup $backup)
    {
        $this->_backup_data = $backup_data;
        parent::__construct($entity_factory, $filesystem);
        $this->_filesystem = $filesystem;
        $this->_backup = $backup;
        $this->_var_directory = $filesystem->get_directory_write(Directory_List::VAR_DIR);
        $this->_hide_backups_for_apache();
        $this->initialize();
    }
    /**
     * Initialize collection
     *
     * @return void
     */
    private function initialize()
    {
        // set collection specific params
        $extensions = $this->_backup_data->get_extensions();
        foreach ($extensions as $value) {
            $extensions[] = '(' . preg_quote($value, '/') . ')';
        }
        $extensions = implode('|', $extensions);
        $this->_var_directory->create($this->_path);
        $path = rtrim($this->_var_directory->get_absolute_path($this->_path), '/') . '/';
        $this->set_order('time', self::SORT_ORDER_DESC)->add_target_dir($path)->set_files_filter('/^[a-z0-9\-\_]+\.' . $extensions . '$/')->set_collect_recursively(false);
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        parent::_reset_state();
        $this->initialize();
    }
    /**
     * Create .htaccess file and deny backups directory access from web
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _hide_backups_for_apache()
    {
        $filename = '.htaccess';
        $driver = $this->_var_directory->get_driver();
        $absolute_path = $driver->get_absolute_path($this->_var_directory->get_absolute_path(), $filename);
        if (!$driver->is_file($absolute_path)) {
            $resource = $driver->file_open($absolute_path, 'w+');
            $driver->file_write($resource, 'deny from all');
            $driver->file_close($resource);
        }
    }
    /**
     * Get backup-specific data from model for each row
     *
     * @param string $filename
     * @return array
     */
    protected function _generate_row($filename)
    {
        $row = parent::_generate_row($filename);
        foreach ($this->_backup->load($row['basename'], $this->_var_directory->get_absolute_path($this->_path))->get_data() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = $this->_var_directory->stat($this->_var_directory->get_relative_path($filename))['size'];
        if (isset($row['display_name']) && $row['display_name'] == '') {
            $row['display_name'] = 'WebSetupWizard';
        }
        $row['id'] = $row['time'] . '_' . $row['type'] . (isset($row['display_name']) ? '_' . $row['display_name'] : '');
        return $row;
    }
}