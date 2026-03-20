<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Backup object factory.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Phrase;
/**
 * @api
 * @since 100.0.2
 */
class Factory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $_object_manager;
    /**
     * Backup type constant for database backup
     */
    public const TYPE_DB = 'db';
    /**
     * Backup type constant for filesystem backup
     */
    public const TYPE_FILESYSTEM = 'filesystem';
    /**
     * Backup type constant for full system backup(database + filesystem)
     */
    public const TYPE_SYSTEM_SNAPSHOT = 'snapshot';
    /**
     * Backup type constant for media and database backup
     */
    public const TYPE_MEDIA = 'media';
    /**
     * Backup type constant for full system backup excluding media folder
     */
    public const TYPE_SNAPSHOT_WITHOUT_MEDIA = 'nomedia';
    /**
     * List of supported a backup types
     *
     * @var string[]
     */
    protected $_allowed_types;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
        $this->_allowed_types = [self::TYPE_DB, self::TYPE_FILESYSTEM, self::TYPE_SYSTEM_SNAPSHOT, self::TYPE_MEDIA, self::TYPE_SNAPSHOT_WITHOUT_MEDIA];
    }
    /**
     * Create new backup instance
     *
     * @param string $type
     * @return BackupInterface
     * @throws LocalizedException
     */
    public function create($type)
    {
        if (!in_array($type, $this->_allowed_types)) {
            throw new Localized_Exception(new Phrase('Current implementation not supported this type (%1) of backup.', [$type]));
        }
        $class = 'Magento\Framework\Backup\\' . ucfirst($type);
        return $this->_object_manager->create($class);
    }
}