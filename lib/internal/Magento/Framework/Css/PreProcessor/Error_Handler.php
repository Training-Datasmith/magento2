<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor;

/**
 * Default Error Handler for CSS files pre-processing
 */
class Error_Handler implements Error_Handler_Interface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\Logger_Interface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * {@inheritdoc}
     */
    public function process_exception(\Exception $e)
    {
        $this->logger->critical($e);
    }
}