<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * A single metric. Type is currently either MEMORY or TIME.
 * This class is an immutable data object.
 */
class Metric
{
    public function __construct(private readonly string $type, private readonly string $name, private readonly mixed $value, private readonly bool $verbose)
    {
    }
    /**
     * Gets type of metric
     */
    public function get_type(): string|int
    {
        return $this->type;
    }
    /**
     * Gets a name
     */
    public function get_name(): string
    {
        return $this->name;
    }
    /**
     * Gets a value
     */
    public function get_value(): mixed
    {
        return $this->value;
    }
    /**
     * Checks if verbose
     */
    public function is_verbose(): bool
    {
        return $this->verbose;
    }
}