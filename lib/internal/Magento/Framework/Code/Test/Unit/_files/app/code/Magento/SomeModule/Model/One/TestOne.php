<?php

declare(strict_types=1);
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

namespace Magento\SomeModule\Model\One;

require_once __DIR__ . '/../Proxy.php';
class TestOne
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy)
    {
        $this->_proxy = $proxy;
    }
}
