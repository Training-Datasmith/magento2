<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\Driver_Pool;
/**
 * The factory of the filesystem directory instances for write operations.
 */
class Write_Factory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driver_pool;
    /**
     * Deny List Validator
     *
     * @var DenyListPathValidator
     */
    private $deny_list_path_validator;
    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     * @param DenyListPathValidator|null $denyListPathValidator
     */
    public function __construct(Driver_Pool $driver_pool, ?Deny_List_Path_Validator $deny_list_path_validator = null)
    {
        $this->driver_pool = $driver_pool;
        $this->deny_list_path_validator = $deny_list_path_validator;
    }
    /**
     * Create a writable directory
     *
     * @param string $path
     * @param string $driverCode
     * @param int $createPermissions
     * @param string $directoryCode
     * @return Write
     */
    public function create($path, $driver_code = Driver_Pool::FILE, $create_permissions = null, $directory_code = null)
    {
        $driver = $this->driver_pool->get_driver($driver_code);
        $factory = new \Magento\Framework\Filesystem\File\Write_Factory($this->driver_pool);
        if ($this->deny_list_path_validator === null) {
            $this->deny_list_path_validator = new Deny_List_Path_Validator($driver);
        }
        $validators = ['pathValidator' => new Path_Validator($driver), 'denyListPathValidator' => $this->deny_list_path_validator];
        $path_validator = new Composite_Path_Validator($validators);
        return new Write($factory, $driver, $path, $create_permissions, $path_validator);
    }
}