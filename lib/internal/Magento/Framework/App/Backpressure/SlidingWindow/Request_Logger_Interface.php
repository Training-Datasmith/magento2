<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\App\Backpressure\Context_Interface;
/**
 * Logs requests
 */
interface Request_Logger_Interface
{
    /**
     * Configuration path to logger type
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER = 'backpressure/logger/type';
    /**
     * Increase counter for requests coming inside given timeslot from given identity
     *
     * @param ContextInterface $context
     * @param int $timeSlot Time slot to increase the counter for (timestamp)
     * @param int $discardAfter Counter for the time slot can be discarded after given number of seconds
     * @return int Requests logged for the identity and the time slot
     */
    public function incr_and_get_for(Context_Interface $context, int $time_slot, int $discard_after): int;
    /**
     * Get counter for specific identity and time slot
     *
     * @param ContextInterface $context
     * @param int $timeSlot
     * @return int|null
     */
    public function get_for(Context_Interface $context, int $time_slot): ?int;
}