<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager_Interface;
use Psr\Log\Logger_Interface;
/**
 * Feed factory
 */
class Feed_Factory implements Feed_Factory_Interface
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $formats;
    /**
     * @param ObjectManagerInterface $objectManger
     * @param LoggerInterface $logger
     * @param array $formats
     */
    public function __construct(Object_Manager_Interface $object_manger, Logger_Interface $logger, array $formats)
    {
        $this->object_manager = $object_manger;
        $this->logger = $logger;
        $this->formats = $formats;
    }
    /**
     * {@inheritdoc}
     */
    public function create(array $data, string $format = Feed_Factory_Interface::FORMAT_RSS): Feed_Interface
    {
        if (!isset($this->formats[$format])) {
            throw new \Magento\Framework\Exception\Input_Exception(new \Magento\Framework\Phrase('The format is not supported'));
        }
        if (!is_subclass_of($this->formats[$format], \Magento\Framework\App\Feed_Interface::class)) {
            throw new \Magento\Framework\Exception\Input_Exception(new \Magento\Framework\Phrase('Wrong format handler type'));
        }
        try {
            return $this->object_manager->create($this->formats[$format], ['data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error($e->get_message());
            throw new \Magento\Framework\Exception\RuntimeException(new \Magento\Framework\Phrase('There has been an error with import'), $e);
        }
    }
}