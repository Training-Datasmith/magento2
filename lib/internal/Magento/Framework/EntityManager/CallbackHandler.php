<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Model\Callback_Pool;
use Psr\Log\Logger_Interface;
/**
 * Class CallbackHandler
 */
class Callback_Handler
{
    /**
     * @var MetadataPool
     */
    protected $metadata_pool;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * CallbackHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param LoggerInterface $logger
     */
    public function __construct(Metadata_Pool $metadata_pool, Logger_Interface $logger)
    {
        $this->metadata_pool = $metadata_pool;
        $this->logger = $logger;
    }
    /**
     * @param string $entityType
     * @throws \Exception
     * @return void
     */
    public function process($entity_type)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $connection = $metadata->get_entity_connection();
        $hash = spl_object_hash($connection);
        if ($connection->get_transaction_level() === 0) {
            $callbacks = Callback_Pool::get($hash);
            try {
                foreach ($callbacks as $callback) {
                    call_user_func($callback);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->get_message(), $e->get_trace());
                throw $e;
            }
        }
    }
    /**
     * @param string $entityType
     * @param array $callback
     * @throws \Exception
     * @return void
     */
    public function attach($entity_type, $callback)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        Callback_Pool::attach(spl_object_hash($metadata->get_entity_connection()), $callback);
    }
    /**
     * @param string $entityType
     * @throws \Exception
     * @return void
     */
    public function clear($entity_type)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        Callback_Pool::clear(spl_object_hash($metadata->get_entity_connection()));
    }
}