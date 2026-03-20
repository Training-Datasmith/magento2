<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderStateResolverInterface
 *
 * @api
 * @since 100.1.2
 */
interface OrderStateResolverInterface
{
    public const IN_PROGRESS = 'order_in_progress';
    public const FORCED_CREDITMEMO = 'forced_creditmemo';

    /**
     * @param OrderInterface $order
     * @param array $arguments
     * @return string
     * @since 100.1.2
     */
    public function getStateForOrder(OrderInterface $order, array $arguments = []);
}
