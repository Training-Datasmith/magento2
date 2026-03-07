<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\CreditmemoResourceInterface;

/**
 * Flat sales order creditmemo resource
 */
class Creditmemo extends SalesResource implements CreditmemoResourceInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_creditmemo', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        $order = $object->getOrder();
        if (!$object->getOrderId() && $order) {
            $object->setOrderId($order->getId());
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress) {
                $object->setBillingAddressId($billingAddress->getId());
            }
        }

        $invoice = $object->getInvoice();
        if (!$object->getInvoiceId() && $invoice) {
            $object->setInvoiceId($invoice->getId());
        }
        return parent::_beforeSave($object);
    }
}
