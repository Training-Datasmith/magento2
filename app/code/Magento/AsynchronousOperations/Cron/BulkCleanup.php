<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Cron;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime;

class BulkCleanup
{
    /**
     * BulkCleanup constructor.
     */
    public function __construct(private readonly MetadataPool $metadataPool, private readonly ResourceConnection $resourceConnection, private readonly DateTime $dateTime, private readonly ScopeConfigInterface $scopeConfig, private readonly \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
    }

    /**
     * Remove all expired bulks and corresponding operations
     */
    public function execute(): void
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        $bulkLifetime = 3600 * 24 * (int)$this->scopeConfig->getValue('system/bulk/lifetime');
        $maxBulkStartTime = $this->dateTime->formatDate($this->date->gmtTimestamp() - $bulkLifetime);
        $connection->delete($metadata->getEntityTable(), ['start_time <= ?' => $maxBulkStartTime]);
    }
}
