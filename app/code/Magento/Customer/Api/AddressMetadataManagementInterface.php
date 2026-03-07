<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api;

/**
 * Interface for managing customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface AddressMetadataManagementInterface extends MetadataManagementInterface
{
    public const ENTITY_TYPE_ADDRESS = 'customer_address';
}
