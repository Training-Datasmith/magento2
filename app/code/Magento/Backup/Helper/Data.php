<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Helper;

use Magento\Backup\Model\Backup;
use Magento\Framework\App\Cache\Type_List_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Helper\Abstract_Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Maintenance_Mode;
use Magento\Framework\Authorization_Interface;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Filesystem;
/**
 * Backup data helper
 * @api
 * @since 100.0.2
 */
class Data extends Abstract_Helper
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;
    /**
     * @var TypeListInterface
     */
    protected $_cache_type_list;
    /**
     * Construct
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param AuthorizationInterface $authorization
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(Context $context, Filesystem $filesystem, Authorization_Interface $authorization, Type_List_Interface $cache_type_list)
    {
        parent::__construct($context);
        $this->_authorization = $authorization;
        $this->_filesystem = $filesystem;
        $this->_cache_type_list = $cache_type_list;
    }
    /**
     * Get all possible backup type values with descriptive title
     *
     * @return array
     */
    public function get_backup_types()
    {
        return [Factory::TYPE_DB => __('Database'), Factory::TYPE_MEDIA => __('Database and Media'), Factory::TYPE_SYSTEM_SNAPSHOT => __('System'), Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('System (excluding Media)')];
    }
    /**
     * Get all possible backup type values
     *
     * @return string[]
     */
    public function get_backup_types_list()
    {
        return [Factory::TYPE_DB, Factory::TYPE_SYSTEM_SNAPSHOT, Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA, Factory::TYPE_MEDIA];
    }
    /**
     * Get default backup type value
     *
     * @return string
     */
    public function get_default_backup_type()
    {
        return Factory::TYPE_DB;
    }
    /**
     * Get directory path where backups stored
     *
     * @return string
     */
    public function get_backups_dir()
    {
        return $this->_filesystem->get_directory_write(Directory_List::VAR_DIR)->get_absolute_path('backups');
    }
    /**
     * Get backup file extension by backup type
     *
     * @param string $type
     * @return string
     */
    public function get_extension_by_type($type)
    {
        $extensions = $this->get_extensions();
        return $extensions[$type] ?? '';
    }
    /**
     * Get all types to extensions map
     *
     * @return array
     */
    public function get_extensions()
    {
        return [Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz', Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz', Factory::TYPE_MEDIA => 'tgz', Factory::TYPE_DB => 'sql'];
    }
    /**
     * Generate backup download name
     *
     * @param Backup $backup
     * @return string
     */
    public function generate_backup_download_name(Backup $backup)
    {
        $additional_extension = $backup->get_type() == Factory::TYPE_DB ? '.sql' : '';
        return $backup->get_time() . '_' . $backup->get_type() . '_' . $backup->get_name() . $additional_extension . '.' . $this->get_extension_by_type($backup->get_type());
    }
    /**
     * Check Permission for Rollback
     *
     * @return bool
     */
    public function is_rollback_allowed()
    {
        return $this->_authorization->is_allowed('Magento_Backup::rollback');
    }
    /**
     * Get paths that should be ignored when creating system snapshots
     *
     * @return string[]
     */
    public function get_backup_ignore_paths()
    {
        return ['.git', '.svn', $this->_filesystem->get_directory_read(Maintenance_Mode::FLAG_DIR)->get_absolute_path(Maintenance_Mode::FLAG_FILENAME), $this->_filesystem->get_directory_read(Directory_List::SESSION)->get_absolute_path(), $this->_filesystem->get_directory_read(Directory_List::CACHE)->get_absolute_path(), $this->_filesystem->get_directory_read(Directory_List::LOG)->get_absolute_path(), $this->_filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('full_page_cache'), $this->_filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('locks'), $this->_filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('report')];
    }
    /**
     * Get paths that should be ignored when rolling back system snapshots
     *
     * @return string[]
     */
    public function get_rollback_ignore_paths()
    {
        return ['.svn', '.git', $this->_filesystem->get_directory_read(Maintenance_Mode::FLAG_DIR)->get_absolute_path(Maintenance_Mode::FLAG_FILENAME), $this->_filesystem->get_directory_read(Directory_List::SESSION)->get_absolute_path(), $this->_filesystem->get_directory_read(Directory_List::LOG)->get_absolute_path(), $this->_filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('locks'), $this->_filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('report'), $this->_filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path('errors'), $this->_filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path('index.php')];
    }
    /**
     * Get backup create success message by backup type
     *
     * @param string $type
     * @return void|string
     */
    public function get_create_success_message_by_type($type)
    {
        $messages_map = [Factory::TYPE_SYSTEM_SNAPSHOT => __('You created the system backup.'), Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('You created the system backup (excluding media).'), Factory::TYPE_MEDIA => __('You created the database and media backup.'), Factory::TYPE_DB => __('You created the database backup.')];
        if (!isset($messages_map[$type])) {
            return;
        }
        return $messages_map[$type];
    }
    /**
     * Invalidate Cache
     *
     * @return $this
     */
    public function invalidate_cache()
    {
        if ($cache_types = $this->_cache_config->get_types()) {
            $cache_types_list = array_keys($cache_types);
            $this->_cache_type_list->invalidate($cache_types_list);
        }
        return $this;
    }
    /**
     * Creates backup's display name from it's name
     *
     * @param string $name
     * @return string
     */
    public function name_to_display_name($name)
    {
        return str_replace('_', ' ', $name);
    }
    /**
     * Extracts information from backup's filename
     *
     * @param string $filename
     * @return \Magento\Framework\DataObject
     */
    public function extract_data_from_filename($filename)
    {
        $extensions = $this->get_extensions();
        $filename_without_extension = $filename ?: '';
        foreach ($extensions as $extension) {
            $filename_without_extension = preg_replace('/' . preg_quote($extension, '/') . '$/', '', $filename_without_extension);
        }
        $filename_without_extension = substr($filename_without_extension, 0, strrpos($filename_without_extension, '.'));
        list($time, $type) = explode('_', $filename_without_extension);
        $name = str_replace($time . '_' . $type, '', $filename_without_extension);
        if (!empty($name)) {
            $name = substr($name, 1);
        }
        $result = new \Magento\Framework\Data_Object();
        $result->add_data(['name' => $name, 'type' => $type, 'time' => $time]);
        return $result;
    }
    /**
     * Is backup functionality enabled.
     *
     * @return bool
     * @since 100.2.6
     */
    public function is_enabled(): bool
    {
        return $this->scope_config->is_set_flag('system/backup/functionality_enabled');
    }
}