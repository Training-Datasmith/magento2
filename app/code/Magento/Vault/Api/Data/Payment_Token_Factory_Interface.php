<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Api\Data;

/**
 * Interface PaymentTokenFactoryInterface
 * @api
 * @since 101.0.0
 */
interface PaymentTokenFactoryInterface
{
    /**
     * Payment Token types
     * @var string
     */
    public const TOKEN_TYPE_ACCOUNT = 'account';
    public const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * Create payment token entity
     * @param $type string|null
     * @return PaymentTokenInterface
     * @since 101.0.0
     */
    public function create($type = null);
}
