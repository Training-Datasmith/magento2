<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Tax\Api\Data\GrandTotalDetailsInterface;

/**
 * Grand Total Tax Details Model
 */
class GrandTotalDetails extends AbstractSimpleObject implements GrandTotalDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    public const AMOUNT = 'amount';
    public const RATES = 'rates';
    public const GROUP_ID = 'group_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        return $this->_get(self::RATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setRates($rates)
    {
        return $this->setData(self::RATES, $rates);
    }
}
