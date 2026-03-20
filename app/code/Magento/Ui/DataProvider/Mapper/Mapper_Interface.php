<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\DataProvider\Mapper;

/**
 * Interface MapperInterface
 *
 * @api
 */
interface MapperInterface
{
    /**
     * Retrieve mapped values
     *
     * @return array
     */
    public function getMappings();
}
