<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Model;

use Magento\Payment\Model\MethodInterface;

/**
 * Interface VaultPaymentInterface
 * @api
 * @since 100.1.0
 */
interface VaultPaymentInterface extends MethodInterface
{
    public const VAULT_AUTHORIZE_COMMAND = 'vault_authorize';

    public const VAULT_SALE_COMMAND = 'vault_sale';

    public const CAN_AUTHORIZE = 'can_authorize_vault';

    public const CAN_CAPTURE = 'can_capture_vault';

    /**
     * @return string|null
     * @since 100.1.0
     */
    public function getProviderCode();
}
