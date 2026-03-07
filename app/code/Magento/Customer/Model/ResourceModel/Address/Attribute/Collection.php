<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Customer Address EAV additional attribute resource collection
 */

namespace Magento\Customer\Model\ResourceModel\Address\Attribute;

class Collection extends \Magento\Customer\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'customer_address';
}
