<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

/**
 * Limit configuration
 */
class Limit_Config
{
    /**
     * @var int
     */
    private int $limit;
    /**
     * @var int
     */
    private int $period;
    /**
     * @param int $limit
     * @param int $period
     */
    public function __construct(int $limit, int $period)
    {
        $this->limit = $limit;
        $this->period = $period;
    }
    /**
     * Requests per period
     *
     * @return int
     */
    public function get_limit(): int
    {
        return $this->limit;
    }
    /**
     * Period in seconds
     *
     * @return int
     */
    public function get_period(): int
    {
        return $this->period;
    }
}