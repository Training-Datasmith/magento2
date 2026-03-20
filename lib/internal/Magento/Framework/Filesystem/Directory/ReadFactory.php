<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\Driver_Pool;
/**
 * The factory of the filesystem directory instances for read operations.
 *
 * @api
 */
class Read_Factory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driver_pool;
    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     */
    public function __construct(Driver_Pool $driver_pool)
    {
        $this->driver_pool = $driver_pool;
    }
    /**
     * Create a readable directory
     *
     * @param string $path
     * @param string $driverCode
     * @return ReadInterface
     */
    public function create($path, $driver_code = Driver_Pool::FILE)
    {
        $driver = $this->driver_pool->get_driver($driver_code);
        $factory = new \Magento\Framework\Filesystem\File\Read_Factory($this->driver_pool);
        return new Read($factory, $driver, $path, new Path_Validator($driver));
    }
}