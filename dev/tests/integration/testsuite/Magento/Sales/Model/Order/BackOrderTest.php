<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class BackOrderTest
 *
 * Integration test to ensure that when the stockState->checkQuoteItemQty call
 * returns a quantity to be backordered, that this value is added to the QuoteItem
 * and saved into the OrderItem
 */
class BackOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_backorder.php
     * @return void
     */
    public function testCreateOrderWithBackorders()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');

        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $checkoutSession->setQuoteId($quote->getId());

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->load($quote->getId(), 'quote_id');
        $cartId = $quoteIdMask->getMaskedId();

        /** @var GuestCartManagementInterface $cartManagement */
        $cartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $orderId = $cartManagement->placeOrder($cartId);

        //The order should have 10 backordered items
        /** @var Order $order */
        $order = $this->objectManager->get(OrderRepository::class)->get($orderId);
        $this->assertNotNull($order);
        $orderitem = $order->getAllItems()[0];
        $this->assertEquals($orderitem->getQtyBackordered(), 10);
    }
}
