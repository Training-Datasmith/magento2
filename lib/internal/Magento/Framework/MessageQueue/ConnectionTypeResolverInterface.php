<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue;

/**
 * Message Queue connection type resolver.
 * @api
 */
interface ConnectionTypeResolverInterface
{
    /**
     * Get connection type by connection name.
     *
     * @param string $connectionName
     * @return string|null
     */
    public function getConnectionType($connectionName);
}
