<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Target_Directory;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Framework\Validation\Validation_Exception;
use Psr\Log\Logger_Interface;
/**
 * File upload class
 *
 * ATTENTION! This class must be used like abstract class and must added
 * validation by protected file extension list to extended class
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @api
 * @since 100.0.2
 */
class Uploader
{
    /**
     * Uploaded file handle (copy of $_FILES[] element)
     *
     * @var array
     * @access protected
     */
    protected $_file;
    /**
     * Uploaded file mime type
     *
     * @var string
     * @access protected
     */
    protected $_file_mime_type;
    /**
     * Upload type. Used to right handle $_FILES array.
     * @var Uploader::SINGLE_STYLE|\Magento\Framework\File\Uploader::MULTIPLE_STYLE
     * @access protected
     */
    protected $_upload_type;
    /**
     * The name of uploaded file. By default it is original file name, but when
     * we will change file name, this variable will be changed too.
     *
     * @var string
     * @access protected
     */
    protected $_uploaded_file_name;
    /**
     * The name of destination directory
     *
     * @var string
     * @access protected
     */
    protected $_uploaded_file_dir;
    /**
     * If this variable is set to TRUE, our library will be able to automatically create
     * non-existent directories.
     *
     * @var bool
     * @access protected
     */
    protected $_allow_create_folders = true;
    /**
     * If this variable is set to TRUE, uploaded file name will be changed if some file with the same
     * name already exists in the destination directory (if enabled).
     *
     * @var bool
     * @access protected
     */
    protected $_allow_rename_files = false;
    /**
     * If this variable is set to TRUE, files dispersion will be supported.
     *
     * @var bool
     * @access protected
     */
    protected $_enable_files_dispersion = false;
    /**
     * This variable is used both with $_enableFilesDispersion == true
     * It helps to avoid problems after migrating from case-insensitive file system to case-insensitive
     * (e.g. NTFS->ext or ext->NTFS)
     *
     * @var bool
     * @access protected
     */
    protected $_case_insensitive_filenames = true;
    /**
     * @var string
     * @access protected
     */
    protected $_dispretion_path = null;
    /**
     * @var bool
     */
    protected $_file_exists = false;
    /**
     * @var null|string[]
     */
    protected $_allowed_extensions = null;
    /**
     * Validate callbacks storage
     *
     * @var array
     * @access protected
     */
    protected $_validate_callbacks = [];
    /**
     * @var \Magento\Framework\File\Mime
     */
    private $file_mime;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**#@+
     * File upload type (multiple or single)
     */
    public const SINGLE_STYLE = 0;
    public const MULTIPLE_STYLE = 1;
    /**#@-*/
    /**
     * Temp file name empty code
     */
    public const TMP_NAME_EMPTY = 666;
    /**
     * Maximum Image Width resolution in pixels. For image resizing on client side
     * @deprecated @see \Magento\Framework\Image\Adapter\UploadConfigInterface::getMaxWidth()
     */
    public const MAX_IMAGE_WIDTH = 1920;
    /**
     * Maximum Image Height resolution in pixels. For image resizing on client side
     * @deprecated @see \Magento\Framework\Image\Adapter\UploadConfigInterface::getMaxHeight()
     */
    public const MAX_IMAGE_HEIGHT = 1200;
    /**
     * Maximum file name length
     */
    private const MAX_FILE_NAME_LENGTH = 255;
    /**
     * Resulting of uploaded file
     *
     * @var array|bool      Array with file info keys: path, file. Result is
     *                      FALSE when file not uploaded
     */
    protected $_result;
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * @var DriverPool|null
     */
    private $driver_pool;
    /**
     * @var DriverInterface|null
     */
    private $file_driver;
    /**
     * @var TargetDirectory
     */
    private $target_directory;
    /**
     * Init upload
     *
     * @param string|array $fileId
     * @param \Magento\Framework\File\Mime|null $fileMime
     * @param DirectoryList|null $directoryList
     * @param DriverPool|null $driverPool
     * @param TargetDirectory|null $targetDirectory
     * @param Filesystem|null $filesystem
     * @throws \DomainException
     */
    public function __construct($file_id, ?Mime $file_mime = null, ?Directory_List $directory_list = null, ?Driver_Pool $driver_pool = null, ?Target_Directory $target_directory = null, ?Filesystem $filesystem = null)
    {
        $this->directory_list = $directory_list ?: Object_Manager::get_instance()->get(Directory_List::class);
        $this->target_directory = $target_directory ?: Object_Manager::get_instance()->get(Target_Directory::class);
        $this->filesystem = $filesystem ?: Object_Manager::get_instance()->get(File_System::class);
        $this->_set_upload_file_id($file_id);
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            throw new \DomainException('The file was not uploaded.', $code);
        } else {
            $this->_file_exists = true;
        }
        $this->file_mime = $file_mime ?: Object_Manager::get_instance()->get(Mime::class);
        $this->driver_pool = $driver_pool ?: Object_Manager::get_instance()->get(Driver_Pool::class);
    }
    /**
     * After save logic
     *
     * @param  array $result
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _after_save($result)
    {
        return $this;
    }
    /**
     * Used to save uploaded file into destination folder with original or new file name (if specified).
     *
     * @param string $destinationFolder
     * @param string $newFileName
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($destination_folder, $new_file_name = null)
    {
        $this->_validate_file();
        $this->validate_destination($destination_folder);
        $this->_result = false;
        $destination_file = $destination_folder;
        $file_name = $new_file_name ?? $this->_file['name'];
        $file_name = static::get_correct_file_name($file_name);
        if ($this->_enable_files_dispersion) {
            $file_name = $this->correct_file_name_case($file_name);
            $this->set_allow_create_folders(true);
            $this->_dispretion_path = static::get_dispersion_path($file_name);
            $destination_file .= $this->_dispretion_path;
            $this->create_destination_folder($destination_file);
        }
        if ($this->_allow_rename_files) {
            $file_name = static::get_new_file_name(static::_add_dir_separator($destination_file) . $file_name);
        }
        $destination_file = static::_add_dir_separator($destination_file) . $file_name;
        try {
            $this->_result = $this->_move_file($this->_file['tmp_name'], $destination_file);
        } catch (\Exception $e) {
            // if the file exists and we had an exception continue anyway
            if (file_exists($destination_file)) {
                $this->_result = true;
            } else {
                throw $e;
            }
        }
        if ($this->_result) {
            if ($this->_enable_files_dispersion) {
                $file_name = str_replace('\\', '/', self::_add_dir_separator($this->_dispretion_path)) . $file_name;
            }
            $this->_uploaded_file_name = $file_name;
            $this->_uploaded_file_dir = $destination_folder;
            $this->_result = $this->_file;
            $this->_result['path'] = $destination_folder;
            $this->_result['file'] = $file_name;
            $this->_after_save($this->_result);
        }
        return $this->_result;
    }
    /**
     * Validates destination directory to be writable
     *
     * @param string $destinationFolder
     * @return void
     * @throws FileSystemException
     */
    private function validate_destination(string $destination_folder): void
    {
        if (strlen($this->get_file_driver()->get_real_path_safety($destination_folder)) > 4096) {
            throw new \InvalidArgumentException('Destination folder path is too long; must be 255 characters or less');
        }
        if ($this->_allow_create_folders) {
            $this->create_destination_folder($destination_folder);
        } elseif (!$this->get_target_directory()->get_directory_write(Directory_List::ROOT)->is_writable($destination_folder)) {
            throw new File_System_Exception(__('Destination folder is not writable or does not exists.'));
        }
    }
    /**
     * Set access permissions to file.
     *
     * @param string $file
     * @return void
     *
     * @deprecated 100.0.8
     * @see Nothing
     */
    protected function chmod($file)
    {
        chmod($file, 0777);
    }
    /**
     * Move files from TMP folder into destination folder
     *
     * @param string $tmpPath
     * @param string $destPath
     * @return bool
     */
    protected function _move_file($tmp_path, $dest_path)
    {
        $root_code = Directory_List::PUB;
        try {
            $path = $this->get_directory_list()->get_path($root_code) ?: '';
            $dest_path = $dest_path ?: '';
            if (strpos($dest_path, $path) !== 0) {
                $root_code = Directory_List::ROOT;
            }
            $dest_path = str_replace($path, '', $dest_path);
            $directory = $this->get_target_directory()->get_directory_write($root_code);
            return $this->get_file_driver()->rename($tmp_path, $directory->get_absolute_path($dest_path), $directory->get_driver());
        } catch (File_System_Exception $exception) {
            $this->get_logger()->critical($exception->get_message());
            return false;
        }
    }
    /**
     * Get logger instance.
     *
     * @return LoggerInterface
     * @deprecated
     * @see Nothing
     */
    private function get_logger(): Logger_Interface
    {
        if (!$this->logger) {
            $this->logger = Object_Manager::get_instance()->get(Logger_Interface::class);
        }
        return $this->logger;
    }
    /**
     * Retrieves target directory.
     *
     * @return TargetDirectory
     */
    private function get_target_directory(): Target_Directory
    {
        if (!isset($this->target_directory)) {
            $this->target_directory = Object_Manager::get_instance()->get(Target_Directory::class);
        }
        return $this->target_directory;
    }
    /**
     * Retrieves directory list.
     *
     * @return DirectoryList
     */
    private function get_directory_list(): Directory_List
    {
        if (!isset($this->directory_list)) {
            $this->directory_list = Object_Manager::get_instance()->get(Directory_List::class);
        }
        return $this->directory_list;
    }
    /**
     * Validate file before save
     *
     * @return void
     * @throws ValidationException
     */
    protected function _validate_file()
    {
        if ($this->_file_exists === false) {
            return;
        }
        //is file extension allowed
        if (!$this->check_allowed_extension($this->get_file_extension())) {
            throw new Validation_Exception(__('Disallowed file type.'));
        }
        //run validate callbacks
        foreach ($this->_validate_callbacks as $params) {
            if (is_object($params['object']) && method_exists($params['object'], $params['method']) && is_callable([$params['object'], $params['method']])) {
                $params['object']->{$params['method']}($this->_file['tmp_name']);
            }
        }
    }
    /**
     * Returns extension of the uploaded file
     *
     * @return string
     */
    public function get_file_extension()
    {
        return $this->_file_exists ? pathinfo($this->_file['name'], PATHINFO_EXTENSION) : '';
    }
    /**
     * Add validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @param object $callbackObject
     * @param string $callbackMethod    Method name of $callbackObject. It must
     *                                  have interface (string $tmpFilePath)
     * @return \Magento\Framework\File\Uploader
     */
    public function add_validate_callback($callback_name, $callback_object, $callback_method)
    {
        $this->_validate_callbacks[$callback_name] = ['object' => $callback_object, 'method' => $callback_method];
        return $this;
    }
    /**
     * Delete validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @access public
     * @return \Magento\Framework\File\Uploader
     */
    public function remove_validate_callback($callback_name)
    {
        if (isset($this->_validate_callbacks[$callback_name])) {
            unset($this->_validate_callbacks[$callback_name]);
        }
        return $this;
    }
    /**
     * Correct filename with special chars and spaces; also trim excessively long filenames
     *
     * @param string $fileName
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function get_correct_file_name($file_name)
    {
        $file_name = $file_name !== null ? ltrim($file_name, '.') : '';
        $file_name = preg_replace('/[^a-z0-9_\-\.]+/i', '_', $file_name);
        $file_info = pathinfo($file_name);
        $file_info['extension'] = $file_info['extension'] ?? '';
        if (strlen($file_info['basename'] ?? '') > self::MAX_FILE_NAME_LENGTH) {
            throw new \LengthException(__('Filename is too long; must be %1 characters or less', self::MAX_FILE_NAME_LENGTH));
        }
        if (preg_match('/^_+$/', $file_info['filename'] ?? '')) {
            $file_name = 'file.' . $file_info['extension'];
        }
        return $file_name;
    }
    /**
     * Convert filename to lowercase in case of case-insensitive file names
     *
     * @param string $fileName
     * @return string
     */
    public function correct_file_name_case($file_name)
    {
        if ($this->_case_insensitive_filenames) {
            return strtolower($file_name);
        }
        return $file_name;
    }
    /**
     * Add directory separator
     *
     * @param string $dir
     * @return string
     */
    protected static function _add_dir_separator($dir)
    {
        if (!$dir || substr($dir, -1) != '/') {
            $dir .= '/';
        }
        return $dir;
    }
    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param string[] $validTypes
     * @access public
     * @return bool
     */
    public function check_mime_type($valid_types = [])
    {
        if (count($valid_types) > 0) {
            if (!in_array($this->_get_mime_type(), $valid_types)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Returns a name of uploaded file
     *
     * @access public
     * @return string
     */
    public function get_uploaded_file_name()
    {
        return $this->_uploaded_file_name;
    }
    /**
     * Used to set {@link _allowCreateFolders} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function set_allow_create_folders($flag)
    {
        $this->_allow_create_folders = $flag;
        return $this;
    }
    /**
     * Used to set {@link _allowRenameFiles} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function set_allow_rename_files($flag)
    {
        $this->_allow_rename_files = $flag;
        return $this;
    }
    /**
     * Used to set {@link _enableFilesDispersion} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function set_files_dispersion($flag)
    {
        $this->_enable_files_dispersion = $flag;
        return $this;
    }
    /**
     * File names Case-sensitivity setter
     *
     * @param bool $flag
     * @return $this
     */
    public function set_filenames_case_sensitivity($flag)
    {
        $this->_case_insensitive_filenames = $flag;
        return $this;
    }
    /**
     * Set allowed extensions
     *
     * @param string[] $extensions
     * @return $this
     */
    public function set_allowed_extensions($extensions = [])
    {
        foreach ((array) $extensions as $extension) {
            $this->_allowed_extensions[] = $extension !== null ? strtolower($extension) : '';
        }
        return $this;
    }
    /**
     * Check if specified extension is allowed
     *
     * @param string $extension
     * @return boolean
     */
    public function check_allowed_extension($extension)
    {
        //File extensions should only be allowed to contain alphanumeric characters
        if ($extension && preg_match('/[^a-z0-9]/i', $extension)) {
            return false;
        }
        if (!is_array($this->_allowed_extensions) || empty($this->_allowed_extensions)) {
            return true;
        }
        return $extension && in_array(strtolower($extension), $this->_allowed_extensions);
    }
    /**
     * Return file mime type
     *
     * @return string
     */
    private function _get_mime_type()
    {
        return $this->file_mime->get_mime_type($this->_file['tmp_name']);
    }
    /**
     * Set upload field id
     *
     * @param string|array $fileId
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException|FileSystemException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _set_upload_file_id($file_id)
    {
        if (is_array($file_id)) {
            $this->validate_file_id($file_id);
            $this->_upload_type = self::MULTIPLE_STYLE;
            $this->_file = $file_id;
        } else {
            if (empty($_FILES)) {
                throw new \DomainException('$_FILES array is empty');
            }
            $file_id = $file_id !== null ? $file_id : '';
            preg_match("/^(.*?)(\\[.+])\$/", $file_id, $file);
            if (is_array($file) && count($file) > 0 && !empty($file[0]) && !empty($file[1])) {
                array_shift($file);
                $this->_upload_type = self::MULTIPLE_STYLE;
                $file_attributes = $_FILES[$file[0]];
                $tmp_var = [];
                foreach ($file_attributes as $attribute_name => $attribute_value) {
                    $keys = explode('][', trim($file[1], '[]'));
                    foreach ($keys as $key) {
                        $key = trim($key, '[]');
                        if (isset($attribute_value[$key])) {
                            $attribute_value = $attribute_value[$key];
                        }
                    }
                    $tmp_var[$attribute_name] = $attribute_value;
                }
                $file_attributes = $tmp_var;
                $this->_file = $file_attributes;
            } elseif (!empty($file_id) && isset($_FILES[$file_id])) {
                $this->_upload_type = self::SINGLE_STYLE;
                $this->_file = $_FILES[$file_id];
            } elseif ($file_id == '') {
                throw new \InvalidArgumentException('Invalid parameter given. A valid $_FILES[] identifier is expected.');
            }
        }
    }
    /**
     * Validates explicitly given uploaded file data.
     *
     * @param array $fileId
     * @return void
     * @throws \InvalidArgumentException
     * @throws FileSystemException
     */
    private function validate_file_id(array $file_id): void
    {
        $is_valid = false;
        if (isset($file_id['tmp_name'])) {
            $tmp_name = trim($file_id['tmp_name']);
            if (preg_match('/\.\.(\\\\|\/)/', $tmp_name) !== 1) {
                $allowed_folders = [sys_get_temp_dir(), $this->directory_list->get_path(Directory_List::SYS_TMP), $this->directory_list->get_path(Directory_List::MEDIA), $this->directory_list->get_path(Directory_List::VAR_DIR), $this->directory_list->get_path(Directory_List::TMP), $this->directory_list->get_path(Directory_List::UPLOAD)];
                $disallowed_folders = [$this->directory_list->get_path(Directory_List::LOG)];
                foreach ($allowed_folders as $allowed_folder) {
                    $dir = $this->target_directory->get_directory_read_by_path($allowed_folder);
                    if ($dir->is_exist($tmp_name)) {
                        $is_valid = true;
                        break;
                    }
                }
                foreach ($disallowed_folders as $disallowed_folder) {
                    $dir = $this->target_directory->get_directory_read_by_path($disallowed_folder);
                    if ($dir->is_exist($tmp_name)) {
                        $is_valid = false;
                        break;
                    }
                }
            }
        }
        if (!$is_valid) {
            throw new \InvalidArgumentException(__('Invalid parameter given. A valid $fileId[tmp_name] is expected.'));
        }
    }
    /**
     * Create destination folder
     *
     * @param string $destinationFolder
     * @return Uploader
     * @throws FileSystemException
     */
    private function create_destination_folder(string $destination_folder)
    {
        if (!$destination_folder) {
            return $this;
        }
        if (substr($destination_folder, -1) == '/') {
            $destination_folder = substr($destination_folder, 0, -1);
        }
        $root_directory = $this->get_target_directory()->get_directory_write(Directory_List::ROOT);
        if (!$root_directory->is_directory($destination_folder)) {
            $result = $root_directory->get_driver()->create_directory($destination_folder);
            if (!$result) {
                throw new File_System_Exception(__('Unable to create directory %1.', $destination_folder));
            }
        }
        return $this;
    }
    /**
     * Get new file name if the same already exists
     *
     * @param string $destinationFile
     * @return string
     */
    public static function get_new_file_name($destination_file)
    {
        /** @var Filesystem $fileSystem */
        $file_system = Object_Manager::get_instance()->get(Filesystem::class);
        $local = $file_system->get_directory_read(Directory_List::ROOT);
        /** @var TargetDirectory $targetDirectory */
        $target_directory = Object_Manager::get_instance()->get(Target_Directory::class);
        $remote = $target_directory->get_directory_read(Directory_List::ROOT);
        $file_exists = function ($path) use ($local, $remote) {
            return $local->is_exist($path) || $remote->is_exist($path);
        };
        $file_info = pathinfo($destination_file);
        $index = 1;
        while ($file_exists($file_info['dirname'] . '/' . $file_info['basename'])) {
            $file_info['basename'] = $file_info['filename'] . '_' . $index++;
            $file_info['basename'] .= isset($file_info['extension']) ? '.' . $file_info['extension'] : '';
        }
        return $file_info['basename'];
    }
    /**
     * Get dispersion path
     *
     * @param string $fileName
     * @return string
     * @deprecated 101.0.4
     * @see Nothing
     */
    public static function get_dispretion_path($file_name)
    {
        return self::get_dispersion_path($file_name);
    }
    /**
     * Get dispersion path
     *
     * @param string $fileName
     * @return string
     * @since 101.0.4
     */
    public static function get_dispersion_path($file_name)
    {
        $char = 0;
        $dispersion_path = '';
        while ($char < 2 && ($file_name && $char < strlen($file_name))) {
            if (empty($dispersion_path)) {
                $dispersion_path = '/' . ('.' == $file_name[$char] ? '_' : $file_name[$char]);
            } else {
                $dispersion_path = self::_add_dir_separator($dispersion_path) . ('.' == $file_name[$char] ? '_' : $file_name[$char]);
            }
            $char++;
        }
        return $dispersion_path;
    }
    /**
     * Get driver for file
     *
     * @return DriverInterface
     * @deprecated
     * @see Nothing
     */
    private function get_file_driver(): Driver_Interface
    {
        if (!$this->file_driver) {
            $this->driver_pool = $this->driver_pool ?: Object_Manager::get_instance()->get(Driver_Pool::class);
            $this->file_driver = $this->driver_pool->get_driver(Driver_Pool::FILE);
        }
        return $this->file_driver;
    }
}