<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory class for \Magento\Backend\Model\View\Result\Redirect
 * @api
 * @since 100.0.2
 */
class Redirect_Factory extends \Magento\Framework\Controller\Result\Redirect_Factory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instance_name;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(Object_Manager_Interface $object_manager, $instance_name = \Magento\Backend\Model\View\Result\Redirect::class)
    {
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create($this->instance_name, $data);
    }
}