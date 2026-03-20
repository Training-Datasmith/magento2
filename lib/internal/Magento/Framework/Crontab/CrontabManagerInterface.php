<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Crontab;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Interface \Magento\Framework\Crontab\CrontabManagerInterface
 *
 * @api
 */
interface Crontab_Manager_Interface
{
    /**#@+
     * Constants for wrapping Magento section in crontab
     */
    public const TASKS_BLOCK_START = '#~ MAGENTO START';
    public const TASKS_BLOCK_END = '#~ MAGENTO END';
    /**#@-*/
    /**
     * Get list of Magento Tasks
     *
     * @return array
     * @throws LocalizedException
     */
    public function get_tasks();
    /**
     * Save Magento Tasks to crontab
     *
     * @param array $tasks
     * @return void
     * @throws LocalizedException
     */
    public function save_tasks(array $tasks);
    /**
     * Remove Magento Tasks form crontab
     *
     * @return void
     * @throws LocalizedException
     */
    public function remove_tasks();
}