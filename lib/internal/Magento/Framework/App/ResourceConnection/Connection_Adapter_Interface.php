<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Resource_Connection;

use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Logger_Interface;
use Magento\Framework\DB\Select_Factory;
/**
 * Connection adapter interface
 *
 * @api
 */
interface Connection_Adapter_Interface
{
    /**
     * Get connection
     *
     * @param LoggerInterface|null $logger
     * @param SelectFactory|null $selectFactory
     * @return AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function get_connection(?Logger_Interface $logger = null, ?Select_Factory $select_factory = null);
}