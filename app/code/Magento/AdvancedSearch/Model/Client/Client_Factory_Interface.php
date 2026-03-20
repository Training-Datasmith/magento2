<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Client;

/**
 * @api
 * @since 100.1.0
 */
interface Client_Factory_Interface
{
    /**
     * Return search client
     *
     * @return ClientInterface
     * @since 100.1.0
     */
    public function create(array $options = []);
}