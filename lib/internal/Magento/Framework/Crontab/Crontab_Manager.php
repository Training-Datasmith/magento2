<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Crontab;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\Shell_Interface;
/**
 * Manager works with cron tasks
 */
class Crontab_Manager implements Crontab_Manager_Interface
{
    /**
     * @var ShellInterface
     */
    private $shell;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @param ShellInterface $shell
     * @param Filesystem $filesystem
     */
    public function __construct(Shell_Interface $shell, Filesystem $filesystem)
    {
        $this->shell = $shell;
        $this->filesystem = $filesystem;
    }
    /**
     * Build tasks block start text.
     *
     * @return string
     */
    private function get_tasks_block_start()
    {
        $tasks_block_start = self::TASKS_BLOCK_START;
        if (defined('BP')) {
            $tasks_block_start .= ' ' . hash('sha256', BP);
        }
        return $tasks_block_start;
    }
    /**
     * Build tasks block end text.
     *
     * @return string
     */
    private function get_tasks_block_end()
    {
        $tasks_block_end = self::TASKS_BLOCK_END;
        if (defined('BP')) {
            $tasks_block_end .= ' ' . hash('sha256', BP);
        }
        return $tasks_block_end;
    }
    /**
     * @inheritdoc
     */
    public function get_tasks()
    {
        $this->check_supported_os();
        $content = $this->get_crontab_content();
        $pattern = '!(' . $this->get_tasks_block_start() . ')(.*?)(' . $this->get_tasks_block_end() . ')!s';
        if (preg_match($pattern, $content, $matches)) {
            $tasks = trim($matches[2] ?? '', PHP_EOL);
            $tasks = explode(PHP_EOL, $tasks);
            return $tasks;
        }
        return [];
    }
    /**
     * @inheritdoc
     */
    public function save_tasks(array $tasks)
    {
        if (!$tasks) {
            throw new Localized_Exception(new Phrase('The list of tasks is empty. Add tasks and try again.'));
        }
        $this->check_supported_os();
        $base_dir = $this->filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path();
        $log_dir = $this->filesystem->get_directory_read(Directory_List::LOG)->get_absolute_path();
        foreach ($tasks as $key => $task) {
            if (empty($task['expression'])) {
                $tasks[$key]['expression'] = '* * * * *';
            }
            if (empty($task['command'])) {
                throw new Localized_Exception(new Phrase("The command shouldn't be empty. Enter and try again."));
            }
            $tasks[$key]['command'] = str_replace(['{magentoRoot}', '{magentoLog}'], [$base_dir, $log_dir], $task['command']);
        }
        $content = $this->get_crontab_content();
        $content = $this->clean_magento_section($content);
        $content = $this->generate_section($content, $tasks);
        $this->save($content);
    }
    /**
     * @inheritdoc
     */
    public function remove_tasks()
    {
        $this->check_supported_os();
        $content = $this->get_crontab_content();
        $content = $this->clean_magento_section($content);
        $this->save($content);
    }
    /**
     * Generate Magento Tasks Section
     *
     * @param string $content
     * @param array $tasks
     * @return string
     */
    private function generate_section($content, $tasks = [])
    {
        if ($tasks) {
            // Add EOL symbol to previous line if not exist.
            if (substr($content, -strlen(PHP_EOL)) !== PHP_EOL) {
                $content .= PHP_EOL;
            }
            $content .= $this->get_tasks_block_start() . PHP_EOL;
            foreach ($tasks as $task) {
                $content .= $task['expression'] . ' ' . PHP_BINARY . ' ' . $task['command'] . PHP_EOL;
            }
            $content .= $this->get_tasks_block_end() . PHP_EOL;
        }
        return $content;
    }
    /**
     * Clean Magento Tasks Section in crontab content
     *
     * @param string $content
     * @return string
     */
    private function clean_magento_section($content)
    {
        $content = preg_replace('!' . preg_quote($this->get_tasks_block_start()) . '.*?' . preg_quote($this->get_tasks_block_end() . PHP_EOL) . '!s', '', $content);
        return $content;
    }
    /**
     * Get crontab content without Magento Tasks Section
     *
     * In case of some exceptions the empty content is returned
     *
     * @return string
     */
    private function get_crontab_content()
    {
        try {
            $content = (string) $this->shell->execute('crontab -l 2>/dev/null');
        } catch (Localized_Exception $e) {
            return '';
        }
        return $content;
    }
    /**
     * Save crontab
     *
     * @param string $content
     * @return void
     * @throws LocalizedException
     */
    private function save($content)
    {
        $content = str_replace(['%', '"', '$'], ['%%', '\"', '\$'], $content);
        try {
            $this->shell->execute('echo "' . $content . '" | crontab -');
        } catch (Localized_Exception $e) {
            throw new Localized_Exception(new Phrase('Error during saving of crontab: %1', [$e->get_previous()->get_message()]), $e);
        }
    }
    /**
     * Check that OS is supported
     *
     * If OS is not supported then no possibility to work with crontab
     *
     * @return void
     * @throws LocalizedException
     */
    private function check_supported_os()
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            throw new Localized_Exception(new Phrase('Your operating system is not supported to work with this command'));
        }
    }
}