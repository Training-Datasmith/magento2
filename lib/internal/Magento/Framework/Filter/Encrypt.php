<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Filter;

use Laminas\Filter\Encrypt as LaminasEncrypt;
use Magento\Framework\Filter\Encrypt\AdapterInterface;

/**
 * Encrypt filter
 */
class Encrypt extends LaminasEncrypt
{
    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct();

        $this->setAdapter($adapter);
    }
}
