<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory class for @see \Magento\Analytics\ReportXml\DB\SelectBuilder
 */
class Select_Builder_Factory
{
    /**
     * SelectBuilderFactory constructor.
     */
    public function __construct(
        /**
         * Object Manager instance
         */
        private readonly Object_Manager_Interface $object_manager
    )
    {
    }
    /**
     * Create class instance with specified parameters
     *
     * @return SelectBuilder
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create(Select_Builder::class, $data);
    }
}