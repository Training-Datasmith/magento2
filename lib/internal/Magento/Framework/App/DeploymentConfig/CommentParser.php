<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Deployment_Config;

use Magento\Framework\App\Config\Comment_Parser_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Filesystem;
/**
 * Parses and retrieves comments from configuration files.
 */
class Comment_Parser implements Comment_Parser_Interface
{
    /**
     * The library to work with file system.
     *
     * @var Filesystem
     */
    private $filesystem;
    /**
     * Stores file key to file name config.
     *
     * @var ConfigFilePool
     */
    private $config_file_pool;
    /**
     * @param Filesystem $filesystem The library to work with file system
     * @param ConfigFilePool $configFilePool Stores file key to file name config
     */
    public function __construct(Filesystem $filesystem, Config_File_Pool $config_file_pool)
    {
        $this->filesystem = $filesystem;
        $this->config_file_pool = $config_file_pool;
    }
    /**
     * Retrieves list of comments from config file.
     *
     * E.g.,
     * ```php
     * [
     *     'modules' => 'Some comment for the modules section'
     *     'system' => 'Some comment for the system section',
     *     ...
     * ]
     * ```
     *
     * The keys of this array are section names to which the comments relate.
     * The values of this array are comments for these sections.
     *
     * If file with provided name does not exist - empty array will be returned.
     *
     * @param string $fileName The name of config file
     * @return array
     */
    public function execute($file_name)
    {
        $result = [];
        $dir_reader = $this->filesystem->get_directory_read(Directory_List::CONFIG);
        if (!$dir_reader->is_exist($file_name)) {
            return $result;
        }
        $file_content = $dir_reader->read_file($file_name);
        $comment_blocks = array_filter(token_get_all($file_content), function ($entry) {
            return T_DOC_COMMENT == $entry[0];
        });
        foreach ($comment_blocks as $comment_block) {
            $text = $this->get_comment_text($comment_block[1]);
            $section = $this->get_section_name($comment_block[1]);
            if ($section && $text) {
                $result[$section] = $text;
            }
        }
        return $result;
    }
    /**
     * Retrieves text of comment.
     *
     * @param string $commentBlock The comment
     * @return string|null
     */
    private function get_comment_text($comment_block)
    {
        $comments_line = [];
        foreach (preg_split("/(\r?\n)/", (string) $comment_block) as $comment_line) {
            if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $comment_line, $matches) && false === strpos($comment_line, 'For the section')) {
                $comments_line[] = preg_replace('/^(\*\s?)/', '', trim($matches[1]));
            }
        }
        return empty($comments_line) ? null : implode(PHP_EOL, $comments_line);
    }
    /**
     * Retrieves section name to which the comment relates.
     *
     * @param string $comment The comment
     * @return string|null
     */
    private function get_section_name($comment)
    {
        $pattern = '/\s+\* For the section: (.+)\s/';
        preg_match_all($pattern, $comment, $matches);
        return empty($matches[1]) ? null : trim(array_shift($matches[1]));
    }
}