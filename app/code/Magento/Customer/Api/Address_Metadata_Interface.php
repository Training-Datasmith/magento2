<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface AddressMetadataInterface extends MetadataInterface
{
    public const ATTRIBUTE_SET_ID_ADDRESS = 2;

    public const ENTITY_TYPE_ADDRESS = 'customer_address';

    public const DATA_INTERFACE_NAME = \Magento\Customer\Api\Data\AddressInterface::class;
}
