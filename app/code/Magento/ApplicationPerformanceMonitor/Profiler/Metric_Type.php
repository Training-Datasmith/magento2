<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * Type of metrics
 */
class Metric_Type
{
    public const OTHER = 'Other';
    public const SECONDS_ELAPSED_FLOAT = 'SecondsElapsedFloat';
    public const UNIX_TIMESTAMP_FLOAT = 'UnixTimestampFloat';
    public const MEMORY_SIZE_INT = 'MemorySizeInt';
}