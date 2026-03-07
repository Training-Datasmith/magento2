<?php

declare(strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\Customer;

/**
 * Customer indexer
 */
class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    public const INDEXER_ID = Customer::CUSTOMER_GRID_INDEXER_ID;
}
