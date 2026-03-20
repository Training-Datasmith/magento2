<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Model\Calculation;

class TotalBaseCalculator extends AbstractAggregateCalculator
{
    /**
     * {@inheritdoc}
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {
        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }
}
