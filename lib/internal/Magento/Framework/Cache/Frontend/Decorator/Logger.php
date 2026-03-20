<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend_Interface;
use Magento\Framework\Cache\Invalidate_Logger as LoggerHandler;
/**
 * Cache frontend decorator that logs cache invalidation actions
 */
class Logger extends Bare
{
    /**
     * @var LoggerHandler
     */
    private $logger;
    /**
     * @param FrontendInterface $frontend
     * @param LoggerHandler $logger
     */
    public function __construct(Frontend_Interface $frontend, Logger_Handler $logger)
    {
        parent::__construct($frontend);
        $this->logger = $logger;
    }
    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        $result = parent::remove($identifier);
        $this->log(compact('identifier'));
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        $result = parent::clean($mode, $tags);
        $this->log(compact('tags', 'mode'));
        return $result;
    }
    /**
     * Log cache invalidation
     *
     * @param mixed $args
     * @return void
     */
    public function log($args)
    {
        $this->logger->execute($args);
    }
}