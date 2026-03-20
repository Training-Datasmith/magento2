<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure;

/**
 * Provides identity for context
 */
interface Identity_Provider_Interface
{
    /**
     * One of ContextInterface constants
     *
     * @return int
     */
    public function fetch_identity_type(): int;
    /**
     * Identity string representation
     *
     * @return string
     */
    public function fetch_identity(): string;
}