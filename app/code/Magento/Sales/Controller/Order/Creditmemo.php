<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\AbstractController\Creditmemo as AbstractCreditmemo;
use Magento\Sales\Controller\OrderInterface;

class Creditmemo extends AbstractCreditmemo implements OrderInterface, HttpGetActionInterface
{
}
