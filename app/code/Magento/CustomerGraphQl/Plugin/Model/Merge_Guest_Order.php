<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Plugin\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\CustomerGraphQl\Model\GetGuestOrdersByEmail;
use Magento\Sales\Model\Order\CustomerAssignment;

class MergeGuestOrder
{
    /**
     * @param GetGuestOrdersByEmail $getGuestOrdersByEmail
     * @param CustomerAssignment $customerAssignment
     */
    public function __construct(
        private readonly GetGuestOrdersByEmail $getGuestOrdersByEmail,
        private readonly CustomerAssignment $customerAssignment
    ) {
    }

    /**
     * Merge guest customer order after signup
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateAccount(AccountManagement $subject, CustomerInterface $customer)
    {
        $searchResult = $this->getGuestOrdersByEmail->execute($customer);
        foreach ($searchResult->getItems() as $order) {
            $this->customerAssignment->execute($order, $customer);
        }
        return $customer;
    }
}
