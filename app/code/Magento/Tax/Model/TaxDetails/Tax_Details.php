<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsInterface;

/**
 * @codeCoverageIgnore
 */
class TaxDetails extends AbstractExtensibleModel implements TaxDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    public const KEY_SUBTOTAL      = 'subtotal';
    public const KEY_TAX_AMOUNT    = 'tax_amount';
    public const KEY_APPLIED_TAXES = 'applied_taxes';
    public const KEY_ITEMS         = 'items';
    public const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getSubtotal()
    {
        return $this->getData(self::KEY_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxAmount()
    {
        return $this->getData(self::KEY_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(self::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(self::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        return $this->setData(self::KEY_SUBTOTAL, $subtotal);
    }

    /**
     * Set tax amount
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::KEY_TAX_AMOUNT, $taxAmount);
    }

    /**
     * Set discount amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
    {
        return $this->setData(
            self::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT,
            $discountTaxCompensationAmount
        );
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(?array $appliedTaxes = null)
    {
        return $this->setData(self::KEY_APPLIED_TAXES, $appliedTaxes);
    }

    /**
     * Set TaxDetails items
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $items
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
