<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Model;

/**
 * Class CreditCardTokenFactory
 * @deprecated 101.0.0
 * @see PaymentTokenFactoryInterface
 */
class CreditCardTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    public const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return self::TOKEN_TYPE_CREDIT_CARD;
    }
}
