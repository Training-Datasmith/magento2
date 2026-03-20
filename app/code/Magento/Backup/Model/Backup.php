<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem\Driver_Pool;
/**
 * Backup file item model
 *
 * @method string getPath()
 * @method string getName()
 * @method string getTime()
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Backup extends \Magento\Framework\Data_Object implements \Magento\Framework\Backup\Db\Backup_Interface
{
    /**
     * Compress rate
     */
    public const COMPRESS_RATE = 9;
    /**
     * Type of backup file
     *
     * @var string
     */
    private $_type = 'db';
    /**
     * Gz file pointer
     *
     * @var \Magento\Framework\Filesystem\File\WriteInterface
     */
    protected $_stream = null;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_helper;
    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale_resolver;
    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backend_auth_session;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $var_directory;
    /**
     * @param \Magento\Backup\Helper\Data $helper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(\Magento\Backup\Helper\Data $helper, \Magento\Framework\Locale\Resolver_Interface $locale_resolver, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Framework\Encryption\Encryptor_Interface $encryptor, \Magento\Framework\Filesystem $filesystem, $data = [])
    {
        $this->_encryptor = $encryptor;
        parent::__construct($data);
        $this->_filesystem = $filesystem;
        $this->var_directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
        $this->_helper = $helper;
        $this->_locale_resolver = $locale_resolver;
        $this->_backend_auth_session = $auth_session;
    }
    /**
     * Set backup time
     *
     * @param int $time
     * @return $this
     */
    public function set_time($time)
    {
        $this->set_data('time', $time);
        return $this;
    }
    /**
     * Set backup path
     *
     * @param string $path
     * @return $this
     */
    public function set_path($path)
    {
        $this->set_data('path', $path);
        return $this;
    }
    /**
     * Set backup name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name)
    {
        $this->set_data('name', $name);
        return $this;
    }
    /**
     * Load backup file info
     *
     * @param string $fileName
     * @param string $filePath
     * @return $this
     */
    public function load($file_name, $file_path)
    {
        $backup_data = $this->_helper->extract_data_from_filename($file_name);
        $this->add_data(['id' => $file_path . '/' . $file_name, 'time' => (int) $backup_data->get_time(), 'path' => $file_path, 'extension' => $this->_helper->get_extension_by_type($backup_data->get_type()), 'display_name' => $this->_helper->name_to_display_name($backup_data->get_name()), 'name' => $backup_data->get_name(), 'date_object' => (new \DateTime())->set_timestamp($backup_data->get_time())]);
        $this->set_type($backup_data->get_type());
        return $this;
    }
    /**
     * Checks backup file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->var_directory->is_file($this->_get_file_path());
    }
    /**
     * Return file name of backup file
     *
     * @return string
     */
    public function get_file_name()
    {
        $filename = $this->get_time() . '_' . $this->get_type();
        $backup_name = $this->get_name();
        if (!empty($backup_name)) {
            $filename .= '_' . $backup_name;
        }
        $filename .= '.' . $this->_helper->get_extension_by_type($this->get_type());
        return $filename;
    }
    /**
     * Sets type of file
     *
     * @param string $value
     * @return $this
     */
    public function set_type($value = 'db')
    {
        $possible_types = $this->_helper->get_backup_types_list();
        if (!in_array($value, $possible_types)) {
            $value = $this->_helper->get_default_backup_type();
        }
        $this->_type = $value;
        $this->set_data('type', $this->_type);
        return $this;
    }
    /**
     * Returns type of backup file
     *
     * @return string
     */
    public function get_type()
    {
        return $this->_type;
    }
    /**
     * Set the backup file content
     *
     * @param string &$content
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function set_file(&$content)
    {
        if (!$this->has_data('time') || !$this->has_data('type') || !$this->has_data('path')) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Please correct the order of creation for a new backup.'));
        }
        $this->var_directory->write_file($this->_get_file_path(), $content);
        return $this;
    }
    /**
     * Return content of backup file
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function &get_file()
    {
        if (!$this->exists()) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('The backup file does not exist.'));
        }
        return $this->var_directory->read($this->_get_file_path());
    }
    /**
     * Delete backup file
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete_file()
    {
        if (!$this->exists()) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('The backup file does not exist.'));
        }
        $this->var_directory->delete($this->_get_file_path());
        return $this;
    }
    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return $this
     * @throws \Magento\Framework\Backup\Exception\NotEnoughPermissions
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function open($write = false)
    {
        if ($this->get_path() === null) {
            throw new \Magento\Framework\Exception\Input_Exception(__('The backup file path was not specified.'));
        }
        if ($write && $this->var_directory->is_file($this->_get_file_path())) {
            $this->var_directory->delete($this->_get_file_path());
        }
        if (!$write && !$this->var_directory->is_file($this->_get_file_path())) {
            throw new \Magento\Framework\Exception\Input_Exception(__('The backup file "%1" does not exist.', $this->get_file_name()));
        }
        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';
        try {
            /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $varDirectory */
            $var_directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR, Driver_Pool::ZLIB);
            $this->_stream = $var_directory->open_file($this->_get_file_path(), $mode);
        } catch (\Magento\Framework\Exception\File_System_Exception $e) {
            throw new \Magento\Framework\Backup\Exception\Not_Enough_Permissions(__('Sorry, but we cannot read from or write to backup file "%1".', $this->get_file_name()));
        }
        return $this;
    }
    /**
     * Get zlib handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function _get_stream()
    {
        if ($this->_stream === null) {
            throw new \Magento\Framework\Exception\Input_Exception(__('The backup file handler was unspecified.'));
        }
        return $this->_stream;
    }
    /**
     * Read backup uncompressed data
     *
     * @param int $length
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    public function read($length)
    {
        return $this->_get_stream()->read($length);
    }
    /**
     * Check end of file.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     */
    public function eof()
    {
        return $this->_get_stream()->eof();
    }
    /**
     * Write to backup file
     *
     * @param string $string
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    public function write($string)
    {
        try {
            $this->_get_stream()->write($string);
        } catch (\Magento\Framework\Exception\File_System_Exception $e) {
            throw new \Magento\Framework\Exception\Input_Exception(__('Something went wrong while writing to the backup file "%1".', $this->get_file_name()));
        }
        return $this;
    }
    /**
     * Close open backup file
     *
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    public function close()
    {
        $this->_get_stream()->close();
        $this->_stream = null;
        return $this;
    }
    /**
     * Print output
     *
     * @return string
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface|string|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function output()
    {
        if (!$this->exists()) {
            return;
        }
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $directory */
        $directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
        $directory = $directory->read_file($this->_get_file_path());
        return $directory;
    }
    /**
     * Get Size
     *
     * @return int|mixed
     */
    public function get_size()
    {
        if ($this->get_data('size') !== null) {
            return $this->get_data('size');
        }
        if ($this->exists()) {
            $this->set_data('size', $this->var_directory->stat($this->_get_file_path())['size']);
            return $this->get_data('size');
        }
        return 0;
    }
    /**
     * Validate user password
     *
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function validate_user_password($password)
    {
        $user_password_hash = $this->_backend_auth_session->get_user()->get_password();
        return $this->_encryptor->validate_hash($password, $user_password_hash);
    }
    /**
     * Get file path.
     *
     * @return string
     */
    protected function _get_file_path()
    {
        return $this->var_directory->get_relative_path($this->get_path() . '/' . $this->get_file_name());
    }
}