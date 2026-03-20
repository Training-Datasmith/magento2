<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Psr\Log\Logger_Interface as Logger;
/**
 * Class AbstractSearchCriteriaBuilder
 *
 * @package Magento\Framework\Data
 */
abstract class Abstract_Search_Criteria_Builder
{
    /**
     * @var ObjectFactory
     */
    protected $object_factory;
    /**
     * @var string
     */
    protected $result_object_interface;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @param Logger $logger
     * @param ObjectFactory $objectFactory
     */
    public function __construct(Logger $logger, Object_Factory $object_factory)
    {
        $this->object_factory = $object_factory;
        $this->logger = $logger;
        $this->init();
    }
    /**
     * Initialization
     *
     * @return string
     */
    abstract protected function init();
    /**
     * Retrieve interface for result
     *
     * @return string
     */
    protected function get_result_object_interface()
    {
        return $this->result_object_interface;
    }
    /**
     * Create result object
     *
     * @return SearchResultInterface
     */
    public function make()
    {
        return $this->object_factory->create($this->get_result_object_interface(), ['queryBuilder' => $this]);
    }
}