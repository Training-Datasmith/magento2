<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

/**
 * Factory class for @see \Magento\Framework\Amqp\Queue
 *
 * @api
 * @since 103.0.0
 */
class Queue_Factory implements \Magento\Framework\Message_Queue\Queue_Factory_Interface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $object_manager = null;
    /**
     * Instance name to create
     *
     * @var string
     */
    private $instance_name = null;
    /**
     * @var ConfigPool
     */
    private $config_pool;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigPool $configPool
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, Config_Pool $config_pool, $instance_name = \Magento\Framework\Amqp\Queue::class)
    {
        $this->object_manager = $object_manager;
        $this->config_pool = $config_pool;
        $this->instance_name = $instance_name;
    }
    /**
     * {@inheritdoc}
     * @since 103.0.0
     */
    public function create($queue_name, $connection_name)
    {
        return $this->object_manager->create($this->instance_name, ['amqpConfig' => $this->config_pool->get($connection_name), 'queueName' => $queue_name]);
    }
}