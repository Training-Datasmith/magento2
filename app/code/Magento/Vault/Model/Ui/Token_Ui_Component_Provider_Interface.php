<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Model\Ui;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface TokenUiComponentProviderInterface
 * @package Magento\Vault\Model\Ui
 * @api
 * @since 100.1.0
 */
interface TokenUiComponentProviderInterface
{
    public const COMPONENT_DETAILS = 'details';
    public const COMPONENT_PUBLIC_HASH = 'publicHash';

    /**
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     * @since 100.1.0
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken);
}
