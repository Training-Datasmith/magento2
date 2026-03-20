<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Aws_S3\Driver;

use Exception;
use Generator;
use League\Flysystem\Config;
use League\Flysystem\Filesystem_Adapter;
use League\Flysystem\Filesystem_Exception as FlysystemFilesystemException;
use League\Flysystem\Unable_To_Retrieve_Metadata;
use League\Flysystem\Visibility;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Phrase;
use Magento\Remote_Storage\Driver\Adapter\Metadata_Provider_Interface;
use Magento\Remote_Storage\Driver\Driver_Exception;
use Magento\Remote_Storage\Driver\Remote_Driver_Interface;
use Psr\Log\Logger_Interface;
use Throwable;
/**
 * Driver for AWS S3 IO operations.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Aws_S3 implements Remote_Driver_Interface
{
    public const TYPE_DIR = 'dir';
    public const TYPE_FILE = 'file';
    private const TEST_FLAG = 'storage.flag';
    private const CONFIG = ['ACL' => 'private', 'visibility' => Visibility::PRIVATE];
    /**
     * @var FilesystemAdapter
     */
    private $adapter;
    private array $streams = [];
    /**
     * @var MetadataProviderInterface
     */
    private $metadata_provider;
    public function __construct(Filesystem_Adapter $adapter, private readonly Logger_Interface $logger, private readonly string $object_url, ?Metadata_Provider_Interface $metadata_provider = null)
    {
        $this->adapter = $adapter;
        $this->metadata_provider = $metadata_provider ?? Object_Manager::get_instance()->get(Metadata_Provider_Interface::class);
    }
    /**
     * Destroy opened streams.
     */
    public function __destruct()
    {
        try {
            foreach ($this->streams as $stream) {
                $this->file_close($stream);
            }
        } catch (Exception $e) {
            // log exception as throwing an exception from a destructor causes a fatal error
            $this->logger->critical($e);
        }
    }
    /**
     * @inheritDoc
     */
    public function test(): void
    {
        try {
            $this->adapter->write(self::TEST_FLAG, '', new Config(self::CONFIG));
        } catch (Exception $exception) {
            throw new Driver_Exception(__($exception->get_message()), $exception);
        }
    }
    /**
     * @inheritDoc
     */
    public function file_get_contents($path, $flag = null, $context = null): string
    {
        $path = $this->normalize_relative_path($path, true);
        if (isset($this->streams[$path])) {
            //phpcs:disable
            return file_get_contents(stream_get_meta_data($this->streams[$path])['uri']);
            //phpcs:enable
        }
        try {
            return $this->adapter->read($path);
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return '';
        }
    }
    /**
     * @inheritDoc
     */
    public function is_exists($path): bool
    {
        if ($path === '/') {
            return true;
        }
        $path = $this->normalize_relative_path($path, true);
        if (!$path) {
            return true;
        }
        try {
            return $this->adapter->file_exists($path);
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
    }
    /**
     * @inheritDoc
     */
    public function is_writable($path): bool
    {
        return true;
    }
    /**
     * @inheritDoc
     */
    public function create_directory($path, $permissions = 0777): bool
    {
        if ($path === '/') {
            return true;
        }
        return $this->create_directory_recursively($path);
    }
    /**
     * Create directory recursively.
     *
     * @throws FileSystemException
     */
    private function create_directory_recursively(string $path): bool
    {
        $path = $this->normalize_relative_path($path);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parent_dir = dirname($path);
        while (!$this->is_directory($parent_dir)) {
            if (!$this->create_directory_recursively($parent_dir)) {
                return false;
            }
        }
        if (!$this->is_directory($path)) {
            try {
                $this->adapter->create_directory($this->fix_path($path), new Config(self::CONFIG));
            } catch (Flysystem_Filesystem_Exception $e) {
                $this->logger->error($e->get_message());
                return false;
            }
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function copy($source, $destination, ?Driver_Interface $target_driver = null): bool
    {
        try {
            $this->adapter->copy($this->normalize_relative_path($source, true), $this->normalize_relative_path($destination, true), new Config(self::CONFIG));
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function delete_file($path): bool
    {
        try {
            $this->adapter->delete($this->normalize_relative_path($path, true));
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function delete_directory($path): bool
    {
        try {
            $this->adapter->delete_directory($this->normalize_relative_path($path, true));
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function file_put_contents($path, $content, $mode = null): bool|int
    {
        $path = $this->normalize_relative_path($path, true);
        $config = self::CONFIG;
        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        if (false !== $image_size = @getimagesizefromstring($content)) {
            $config['Metadata'] = ['image-width' => $image_size[0], 'image-height' => $image_size[1]];
        }
        try {
            $this->adapter->write($path, $content, new Config($config));
            return $this->adapter->file_size($path)->file_size() !== null ?? true;
        } catch (Flysystem_Filesystem_Exception|Unable_To_Retrieve_Metadata $e) {
            $this->logger->error($e->get_message());
            return false;
        }
    }
    /**
     * @inheritDoc
     */
    public function read_directory_recursively($path = null): array
    {
        return $this->read_path($path, true);
    }
    /**
     * @inheritDoc
     */
    public function read_directory($path): array
    {
        return $this->read_path($path, false);
    }
    /**
     * @inheritDoc
     */
    public function get_real_path_safety($path): string|array|null
    {
        //Removing redundant directory separators
        $path = preg_replace('~(?<!:)\/\/+~', '/', $path);
        if (!str_contains((string) $path, '/.')) {
            return $path;
        }
        $is_absolute = str_starts_with((string) $path, $this->normalize_absolute_path(''));
        $path = $this->normalize_relative_path($path);
        $path_parts = explode('/', $path);
        if (end($path_parts) === '.') {
            $path_parts[count($path_parts) - 1] = '';
        }
        $real_path = [];
        foreach ($path_parts as $path_part) {
            if ($path_part === '.') {
                continue;
            }
            if ($path_part === '..') {
                array_pop($real_path);
                continue;
            }
            $real_path[] = $path_part;
        }
        if ($is_absolute) {
            return $this->normalize_absolute_path(implode('/', $real_path));
        }
        return implode('/', $real_path);
    }
    /**
     * @inheritDoc
     */
    public function get_absolute_path($base_path, $path, $scheme = null): string
    {
        $base_path = (string) $base_path;
        $path = (string) $path;
        if ($base_path && $path && str_starts_with(rtrim($path, '/'), rtrim($base_path, '/'))) {
            return $this->normalize_absolute_path($path);
        }
        if ($base_path) {
            $path = $base_path . ltrim($path, '/');
        }
        return $this->normalize_absolute_path($path);
    }
    /**
     * Resolves relative path.
     *
     * @param string $path Absolute path
     * @return string Relative path
     */
    private function normalize_relative_path(string $path, bool $fix_path = false): string
    {
        $relative_path = str_replace($this->normalize_absolute_path(''), '', $path);
        if ($fix_path) {
            return $this->fix_path($relative_path);
        }
        return $relative_path;
    }
    /**
     * Resolves absolute path.
     *
     * @param string $path Relative path
     * @return string Absolute path
     */
    private function normalize_absolute_path(string $path): string
    {
        $path = str_replace($this->get_object_url(''), '', $path);
        return $this->get_object_url($path);
    }
    /**
     * Retrieves object URL from cache.
     */
    private function get_object_url(string $path): string
    {
        return $this->object_url . ltrim($path, '/');
    }
    /**
     * @inheritDoc
     */
    public function is_readable($path): bool
    {
        return $this->is_exists($path);
    }
    /**
     * Check is specified path a file.
     *
     * @return bool
     */
    private function is_type_file(string $path)
    {
        try {
            $metadata = $this->metadata_provider->get_metadata($this->normalize_relative_path($path, true));
            if ($metadata && isset($metadata['type'])) {
                return $metadata['type'] === self::TYPE_FILE;
            }
        } catch (Unable_To_Retrieve_Metadata) {
            return false;
        }
        return false;
    }
    /**
     * @inheritDoc
     */
    public function is_file($path): bool
    {
        if (!$path || $path === '/') {
            return false;
        }
        return $this->is_type_file($path);
    }
    /**
     * @inheritDoc
     */
    public function is_directory($path): bool
    {
        if (in_array($path, ['.', '/', ''], true)) {
            return true;
        }
        if (!$path) {
            return true;
        }
        return $this->is_type_directory($path);
    }
    /**
     * Check is given path a directory in metadata.
     */
    private function is_type_directory(string $path): bool
    {
        try {
            $meta = $this->metadata_provider->get_metadata($this->normalize_relative_path($path, true));
        } catch (Unable_To_Retrieve_Metadata) {
            return false;
        }
        if (isset($meta['type']) && $meta['type'] === self::TYPE_DIR) {
            return true;
        }
        return false;
    }
    /**
     * Check if directory exists by path.
     */
    private function directory_exists(string $path): bool
    {
        try {
            return $this->adapter->file_exists($path);
        } catch (Throwable) {
            // catch closed iterator
            return false;
        }
    }
    /**
     * @inheritDoc
     */
    public function get_relative_path($base_path, $path = null): string
    {
        $base_path = (string) $base_path;
        $path = (string) $path;
        if ($base_path && $path && ($base_path === $path . '/' || str_starts_with($path, $base_path))) {
            return substr($path, strlen($base_path));
        }
        return $path;
    }
    /**
     * @inheritDoc
     */
    public function get_parent_directory($path): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return rtrim(dirname($this->normalize_absolute_path($path)), '/') . '/';
    }
    /**
     * @inheritDoc
     */
    public function get_real_path($path): string
    {
        return $this->normalize_absolute_path($path);
    }
    /**
     * @inheritDoc
     */
    public function rename($old_path, $new_path, ?Driver_Interface $target_driver = null): bool
    {
        if ($old_path === $new_path) {
            return true;
        }
        try {
            $this->adapter->move($this->normalize_relative_path($old_path, true), $this->normalize_relative_path($new_path, true), new Config(self::CONFIG));
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function stat($path): array
    {
        $result = ['dev' => 0, 'ino' => 0, 'mode' => 0, 'nlink' => 0, 'uid' => 0, 'gid' => 0, 'rdev' => 0, 'atime' => 0, 'ctime' => 0, 'blksize' => 0, 'blocks' => 0, 'size' => 0, 'type' => '', 'mtime' => 0, 'disposition' => null];
        $path = $this->normalize_relative_path($path, true);
        try {
            $meta_info = $this->metadata_provider->get_metadata($path);
        } catch (Unable_To_Retrieve_Metadata) {
            if ($this->directory_exists($path)) {
                $result['type'] = self::TYPE_DIR;
            }
            return $result;
        }
        if (!$meta_info) {
            throw new File_System_Exception(__('Cannot gather stats! %1', [$this->get_warning_message()]));
        }
        if ($meta_info['type'] === 'file') {
            $result['size'] = $meta_info['size'];
            $result['type'] = $meta_info['type'];
            $result['mtime'] = $meta_info['timestamp'];
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function get_metadata(string $path): array
    {
        return $this->metadata_provider->get_metadata($this->normalize_relative_path($path));
    }
    /**
     * @inheritDoc
     */
    public function search($pattern, $path): array
    {
        return iterator_to_array($this->glob(rtrim((string) $path, '/') . '/' . ltrim((string) $pattern, '/')), false);
    }
    /**
     * Emulate php glob function for AWS S3 storage
     *
     * @throws FileSystemException
     */
    private function glob(string $pattern): Generator
    {
        $pattern_found = preg_match('(\*|\?|\[.+\])', $pattern, $parent_pattern, PREG_OFFSET_CAPTURE);
        if ($pattern_found) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $parent_directory = dirname(substr($pattern, 0, $parent_pattern[0][1] + 1));
            $leftover = substr($pattern, $parent_pattern[0][1]);
            $index = strpos($leftover, '/');
            $search_pattern = $this->get_search_pattern($pattern, $parent_pattern, $parent_directory, $index);
            if ($this->is_directory($parent_directory)) {
                yield from $this->get_directory_content($parent_directory, $search_pattern, $leftover, $index);
            }
        } elseif ($this->is_exists($pattern)) {
            yield $this->normalize_absolute_path($pattern);
        }
    }
    /**
     * @inheritDoc
     */
    public function symlink($source, $destination, ?Driver_Interface $target_driver = null): bool
    {
        return $this->copy($source, $destination, $target_driver);
    }
    /**
     * @inheritDoc
     */
    public function change_permissions($path, $permissions): bool
    {
        return true;
    }
    /**
     * @inheritDoc
     */
    public function change_permissions_recursively($path, $dir_permissions, $file_permissions): bool
    {
        return true;
    }
    /**
     * @inheritDoc
     */
    public function touch($path, $modification_time = null): bool
    {
        $path = $this->normalize_relative_path($path, true);
        try {
            $content = $this->adapter->file_exists($path) ? $this->adapter->read($path) : '';
            $this->adapter->write($path, $content, new Config([]));
        } catch (Flysystem_Filesystem_Exception $e) {
            $this->logger->error($e->get_message());
            return false;
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function file_read_line($resource, $length, $ending = null): string
    {
        // phpcs:disable
        $result = @stream_get_line($resource, $length, (string) $ending);
        // phpcs:enable
        if (false === $result) {
            throw new File_System_Exception(new Phrase('File cannot be read %1', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_read($resource, $length): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = fread($resource, $length);
        if ($result === false) {
            throw new File_System_Exception(__('File cannot be read %1', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_get_csv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = fgetcsv($resource, $length, $delimiter, $enclosure, $escape);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('The "%1" CSV handle is incorrect. Verify the handle and try again.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_tell($resource): int
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction, Generic.PHP.NoSilencedErrors.Discouraged
        $result = @ftell($resource);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_seek($resource, $offset, $whence = SEEK_SET): int
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction, Generic.PHP.NoSilencedErrors.Discouraged
        $result = @fseek($resource, $offset, $whence);
        if ($result === -1) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileSeek execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function end_of_file($resource): bool
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        return feof($resource);
    }
    /**
     * @inheritDoc
     */
    public function file_put_csv($resource, array $data, $delimiter = ',', $enclosure = '"'): int|false
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return fputcsv($resource, $data, $delimiter, $enclosure, '\\');
    }
    /**
     * @inheritDoc
     */
    public function file_flush($resource): bool
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction, Generic.PHP.NoSilencedErrors.Discouraged
        $result = @fflush($resource);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileFlush execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_lock($resource, $lock_mode = LOCK_EX): bool
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction, Generic.PHP.NoSilencedErrors.Discouraged
        $result = @flock($resource, $lock_mode);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileLock execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_unlock($resource): bool
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction, Generic.PHP.NoSilencedErrors.Discouraged
        $result = @flock($resource, LOCK_UN);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileUnlock execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function file_write($resource, $data): int|false
    {
        //phpcs:disable
        $resource_path = stream_get_meta_data($resource)['uri'];
        //phpcs:enable
        foreach ($this->streams as $stream) {
            //phpcs:disable
            if (stream_get_meta_data($stream)['uri'] === $resource_path) {
                return fwrite($stream, $data);
            }
            //phpcs:enable
        }
        return false;
    }
    /**
     * @inheritDoc
     */
    public function file_close($resource): bool
    {
        if (!is_resource($resource)) {
            return false;
        }
        //phpcs:disable
        $meta = stream_get_meta_data($resource);
        //phpcs:enable
        foreach ($this->streams as $path => $stream) {
            // phpcs:ignore
            if (stream_get_meta_data($stream)['uri'] === $meta['uri']) {
                if (isset($meta['seekable']) && $meta['seekable']) {
                    // rewind the file pointer to make sure the full content of the file is saved
                    $this->file_seek($resource, 0);
                }
                $this->adapter->write_stream($path, $resource, new Config(self::CONFIG));
                // Remove path from streams after
                unset($this->streams[$path]);
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
                return fclose($stream);
            }
        }
        return false;
    }
    /**
     * @inheritDoc
     */
    public function file_open($path, $mode)
    {
        $_mode = str_replace(['b', '+'], '', strtolower($mode));
        if (!in_array($_mode, ['r', 'w', 'a'], true)) {
            throw new File_System_Exception(new Phrase('Invalid file open mode "%1".', [$mode]));
        }
        $path = $this->normalize_relative_path($path, true);
        if (!isset($this->streams[$path])) {
            $this->streams[$path] = tmpfile();
            try {
                if ($this->adapter->file_exists($path)) {
                    if ($_mode !== 'w') {
                        //phpcs:ignore Magento2.Functions.DiscouragedFunction
                        fwrite($this->streams[$path], (string) $this->adapter->read($path));
                        //phpcs:ignore Magento2.Functions.DiscouragedFunction
                        if ($_mode !== 'a') {
                            rewind($this->streams[$path]);
                        }
                    }
                }
            } catch (Flysystem_Filesystem_Exception $e) {
                $this->logger->error($e->get_message());
            }
        }
        return $this->streams[$path];
    }
    /**
     * Removes slashes in path.
     */
    private function fix_path(string $path): string
    {
        return trim($path, '/');
    }
    /**
     * Returns last warning message string
     */
    private function get_warning_message(): ?string
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] === E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }
    /**
     * Read directory by path and is recursive flag
     */
    private function read_path(string $path, bool $is_recursive = false): array
    {
        $relative_path = $this->normalize_relative_path($path);
        $items_list = [];
        foreach ($this->adapter->list_contents($this->fix_path($relative_path), $is_recursive) as $item) {
            $path = $item->path();
            if (!empty($path) && $path !== $relative_path && (!$relative_path || str_starts_with((string) $path, $relative_path))) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $items_list[] = $this->get_absolute_path(dirname((string) $path), $path);
            }
        }
        return $items_list;
    }
    /**
     * Get search pattern for directory
     */
    private function get_search_pattern(string $pattern, array $parent_pattern, string $parent_directory, int|bool $index): string
    {
        $parent_length = strlen($parent_directory);
        if ($index !== false) {
            $search_pattern = substr($pattern, $parent_length + 1, $parent_pattern[0][1] - $parent_length + $index - 1);
        } else {
            $search_pattern = substr($pattern, $parent_length + 1);
        }
        $replacement = ['/\*/' => '.*', '/\?/' => '.', '/\//' => '\/'];
        return preg_replace(array_keys($replacement), array_values($replacement), $search_pattern);
    }
    /**
     * Get directory content by given search pattern
     *
     * @throws FileSystemException
     */
    private function get_directory_content(string $parent_directory, string $search_pattern, string $leftover, int|bool $index): Generator
    {
        $items = $this->read_directory($parent_directory);
        $directory_content = [];
        foreach ($items as $item) {
            if (preg_match('/' . $search_pattern . '$/', (string) $item) && !str_starts_with(basename((string) $item), '.')) {
                if ($index === false || strlen($leftover) === $index + 1) {
                    yield $this->normalize_absolute_path($this->is_directory($item) ? rtrim((string) $item, '/') . '/' : $item);
                } elseif (strlen($leftover) > $index + 1) {
                    yield from $this->glob("{$parent_directory}/{$item}" . substr($leftover, $index));
                }
            }
        }
        return $directory_content;
    }
}