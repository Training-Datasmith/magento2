<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestModuleSynchronousAmqp\Api;

interface ServiceInterface
{
    /**
     * @param string $simpleDataItem
     * @return string
     */
    public function execute($simpleDataItem);
}
