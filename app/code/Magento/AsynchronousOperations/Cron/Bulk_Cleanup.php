<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Cron;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Stdlib\DateTime;
class Bulk_Cleanup
{
    /**
     * BulkCleanup constructor.
     */
    public function __construct(private readonly Metadata_Pool $metadata_pool, private readonly Resource_Connection $resource_connection, private readonly DateTime $date_time, private readonly Scope_Config_Interface $scope_config, private readonly \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
    }
    /**
     * Remove all expired bulks and corresponding operations
     */
    public function execute(): void
    {
        $metadata = $this->metadata_pool->get_metadata(Bulk_Summary_Interface::class);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        $bulk_lifetime = 3600 * 24 * (int) $this->scope_config->get_value('system/bulk/lifetime');
        $max_bulk_start_time = $this->date_time->format_date($this->date->gmt_timestamp() - $bulk_lifetime);
        $connection->delete($metadata->get_entity_table(), ['start_time <= ?' => $max_bulk_start_time]);
    }
}