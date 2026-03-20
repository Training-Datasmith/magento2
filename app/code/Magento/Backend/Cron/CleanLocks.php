<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Cron;

use Magento\Framework\Lock\Backend\File_Lock;
use Magento\Framework\Lock\Lock_Backend_Factory;
use Psr\Log\Logger_Interface;
class Clean_Locks
{
    /**
     * @param LockBackendFactory $lockFactory
     * @param LoggerInterface $logger
     */
    public function __construct(private readonly Lock_Backend_Factory $lock_factory, private readonly Logger_Interface $logger)
    {
    }
    /**
     * Cron job to cleanup old locks
     */
    public function execute(): void
    {
        $locker = $this->lock_factory->create();
        if ($locker instanceof File_Lock) {
            $number_of_lock_files_deleted = $locker->cleanup_old_locks();
            $this->logger->info(sprintf('Deleted %d old lock files', $number_of_lock_files_deleted));
        }
    }
}