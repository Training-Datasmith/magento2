<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
/**
 * Migrate legacy cron config paths for analytics jobs from the "default" group to the "analytics" group.
 */
class Move_Cron_Config_To_Analytics_Group implements Data_Patch_Interface
{
    public function __construct(private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * @inheritDoc
     */
    public function apply(): static
    {
        $connection = $this->resource_connection->get_connection();
        $table = $this->resource_connection->get_table_name('core_config_data');
        // Find all analytics cron rows under the "default" cron group
        $select = $connection->select()->from($table, ['config_id', 'scope', 'scope_id', 'path', 'value'])->where('path LIKE ?', 'crontab/default/jobs/analytics_%');
        $rows = (array) $connection->fetch_all($select);
        foreach ($rows as $row) {
            $old_path = (string) $row['path'];
            $new_path = (string) preg_replace('#^crontab/default/#', 'crontab/analytics/', $old_path);
            $connection->update($table, ['path' => $new_path], ['config_id = ?' => (int) $row['config_id']]);
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public static function get_dependencies(): array
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_aliases(): array
    {
        return [];
    }
}