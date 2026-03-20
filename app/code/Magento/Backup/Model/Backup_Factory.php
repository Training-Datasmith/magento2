<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Backup model factory
 *
 * @method \Magento\Backup\Model\Backup create($timestamp, $type)
 */
namespace Magento\Backup\Model;

/**
 * @api
 * @since 100.0.2
 */
class Backup_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Load backup by it's type and creation timestamp
     *
     * @param int $timestamp
     * @param string $type
     * @return \Magento\Backup\Model\Backup
     */
    public function create($timestamp, $type)
    {
        $fs_collection = $this->_object_manager->create(\Magento\Backup\Model\Fs\Collection::class);
        $backup_instance = $this->_object_manager->create(\Magento\Backup\Model\Backup::class);
        foreach ($fs_collection as $backup) {
            if ($backup->get_time() === (int) $timestamp && $backup->get_type() === $type) {
                $backup_instance->set_data(['id' => $backup->get_id()])->set_type($backup->get_type())->set_time($backup->get_time())->set_name($backup->get_name())->set_path($backup->get_path());
                break;
            }
        }
        return $backup_instance;
    }
}