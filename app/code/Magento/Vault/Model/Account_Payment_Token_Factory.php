<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Model;

/**
 * Class AccountPaymentTokenFactory
 * @deprecated 101.0.0
 * @see PaymentTokenFactoryInterface
 */
class AccountPaymentTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    public const TOKEN_TYPE_ACCOUNT = 'account';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return self::TOKEN_TYPE_ACCOUNT;
    }
}
