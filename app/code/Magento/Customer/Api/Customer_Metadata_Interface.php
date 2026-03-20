<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer attributes metadata.
 * @api
 * @since 100.0.2
 */
interface CustomerMetadataInterface extends MetadataInterface
{
    public const ATTRIBUTE_SET_ID_CUSTOMER = 1;

    public const ENTITY_TYPE_CUSTOMER = 'customer';

    public const DATA_INTERFACE_NAME = \Magento\Customer\Api\Data\CustomerInterface::class;
}
