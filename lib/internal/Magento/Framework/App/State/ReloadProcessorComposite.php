<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\State;

/**
 * Composite of reload processors
 */
class Reload_Processor_Composite implements Reload_Processor_Interface
{
    /**
     * @param ReloadProcessorInterface[] $processors
     */
    public function __construct(private array $processors)
    {
        ksort($this->processors, SORT_STRING);
    }
    /**
     * @inheritdoc
     */
    public function reload_state(): void
    {
        /** @var ReloadProcessorInterface $processor */
        foreach ($this->processors as $processor) {
            $processor->reload_state();
        }
    }
}