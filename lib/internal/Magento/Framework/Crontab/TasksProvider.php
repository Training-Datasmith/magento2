<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Crontab;

/**
 * TasksProvider collects list of tasks
 */
class Tasks_Provider implements Tasks_Provider_Interface
{
    /**
     * @var array
     */
    private $tasks = [];
    /**
     * @param array $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }
    /**
     * {@inheritdoc}
     */
    public function get_tasks()
    {
        return $this->tasks;
    }
}