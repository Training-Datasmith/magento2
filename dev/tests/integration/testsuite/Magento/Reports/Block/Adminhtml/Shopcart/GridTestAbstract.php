<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Block\Adminhtml\Shopcart;

use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

abstract class GridTestAbstract extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById(1);

        /** @var Quote $quoteFixture */
        $quoteFixture = $objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setIsActive(true);
        $quoteFixture->setCustomer($customerData);
        $quoteFixture->save();
    }
}
