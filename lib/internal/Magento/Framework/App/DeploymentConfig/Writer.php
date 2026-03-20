<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Deployment_Config;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Phrase;
/**
 * Deployment configuration writer to files: env.php, config.php.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Writer
{
    /**
     * Deployment config reader
     *
     * @var Reader
     */
    private $reader;
    /**
     * Application filesystem
     *
     * @var Filesystem
     */
    private $filesystem;
    /**
     * Formatter
     *
     * @var Writer\FormatterInterface
     */
    private $formatter;
    /**
     * @var ConfigFilePool
     */
    private $config_file_pool;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * The parser of comments from configuration files.
     *
     * @var CommentParser
     */
    private $comment_parser;
    /**
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param DeploymentConfig $deploymentConfig
     * @param Writer\FormatterInterface|null $formatter
     * @param CommentParser|null $commentParser The parser of comments from configuration files
     */
    public function __construct(Reader $reader, Filesystem $filesystem, Config_File_Pool $config_file_pool, Deployment_Config $deployment_config, ?Writer\Formatter_Interface $formatter = null, ?Comment_Parser $comment_parser = null)
    {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->config_file_pool = $config_file_pool;
        $this->deployment_config = $deployment_config;
        $this->formatter = $formatter ?: new Writer\Php_Formatter();
        $this->comment_parser = $comment_parser ?: new Comment_Parser($filesystem, $config_file_pool);
    }
    /**
     * Check if configuration file is writable
     *
     * @return bool
     */
    public function check_if_writable()
    {
        $config_directory = $this->filesystem->get_directory_write(Directory_List::CONFIG);
        foreach ($this->reader->get_files() as $file) {
            if (!$config_directory->is_writable($file)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Saves config in specified file.
     * $pool option is deprecated since version 2.2.0.
     *
     * Usage:
     * ```php
     * saveConfig(
     *      [
     *          ConfigFilePool::APP_ENV => ['some' => 'value'],
     *      ],
     *      true,
     *      null,
     *      [],
     *      false
     * )
     * ```
     *
     * @param array $data The data to be saved
     * @param bool $override Whether values should be overridden
     * @param string $pool The file pool (deprecated)
     * @param array $comments The array of comments
     * @param bool $lock Whether the file should be locked while writing
     * @return void
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save_config(array $data, $override = false, $pool = null, array $comments = [], bool $lock = false)
    {
        foreach ($data as $file_key => $config) {
            $paths = $this->config_file_pool->get_paths();
            if (isset($paths[$file_key])) {
                $current_data = $this->reader->load($file_key);
                $current_comments = $this->comment_parser->execute($paths[$file_key]);
                if ($current_data) {
                    if ($override) {
                        $config = array_merge($current_data, $config);
                    } else {
                        $config = array_replace_recursive($current_data, $config);
                    }
                }
                $comments = array_merge($current_comments, $comments);
                $contents = $this->formatter->format($config, $comments);
                try {
                    $write_file_path = $paths[$file_key];
                    $directory_write = $this->filesystem->get_directory_write(Directory_List::CONFIG);
                    if ($directory_write instanceof Write) {
                        $directory_write->write_file($write_file_path, $contents, 'w+', $lock);
                    } else {
                        $directory_write->write_file($write_file_path, $contents);
                    }
                } catch (File_System_Exception $e) {
                    throw new File_System_Exception(new Phrase('The "%1" deployment config file isn\'t writable.', [$paths[$file_key]]));
                }
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($this->filesystem->get_directory_read(Directory_List::CONFIG)->get_absolute_path($paths[$file_key]));
                }
            }
        }
        $this->deployment_config->reset_data();
    }
}