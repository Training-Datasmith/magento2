<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\Model\Resource_Model\Viewer;

use Magento\Admin_Analytics\Model\Viewer\Log;
use Magento\Admin_Analytics\Model\Viewer\Log_Factory;
use Magento\Framework\App\Resource_Connection;
/**
 * Admin Analytics log data logger.
 *
 * Saves and retrieves release notification viewer log data.
 */
class Logger
{
    /**
     * Admin Analytics usage version log table name
     */
    public const LOG_TABLE_NAME = 'admin_analytics_usage_version_log';
    /**
     * @var LogFactory
     */
    private $log_factory;
    public function __construct(private readonly Resource_Connection $resource, Log_Factory $log_factory)
    {
        $this->log_factory = $log_factory;
    }
    /**
     * Save (insert new or update existing) log.
     */
    public function log(string $last_view_version): bool
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->get_connection(Resource_Connection::DEFAULT_CONNECTION);
        $connection->insert_on_duplicate($this->resource->get_table_name(self::LOG_TABLE_NAME), ['last_viewed_in_version' => $last_view_version], ['last_viewed_in_version']);
        return true;
    }
    /**
     * Get log by the last view version.
     */
    public function get(): Log
    {
        return $this->log_factory->create(['data' => $this->load_latest_log_data()]);
    }
    /**
     * Checks is log already exists.
     */
    public function check_log_exists(): bool
    {
        $data = $this->log_factory->create(['data' => $this->load_latest_log_data()]);
        $last_viewed_version = $data->get_last_view_version();
        return isset($last_viewed_version);
    }
    /**
     * Load release notification viewer log data by last view version
     */
    private function load_latest_log_data(): array
    {
        $connection = $this->resource->get_connection();
        $select = $connection->select()->from(['log_table' => $this->resource->get_table_name(self::LOG_TABLE_NAME)])->order('log_table.id desc')->limit(['count' => 1]);
        $data = $connection->fetch_row($select);
        if (!$data) {
            return [];
        }
        return $data;
    }
}