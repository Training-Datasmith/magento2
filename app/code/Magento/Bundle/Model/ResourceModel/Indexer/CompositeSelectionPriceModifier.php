<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

class Composite_Selection_Price_Modifier implements Selection_Price_Modifier_Interface
{
    /**
     * @param SelectionPriceModifierInterface[] $modifiers
     */
    public function __construct(private readonly array $modifiers = [])
    {
        // Validate that all modifiers implement the correct interface
        array_map(fn(Selection_Price_Modifier_Interface $modifier) => $modifier, $this->modifiers);
    }
    /**
     * @inheritDoc
     */
    public function modify(string $index_table, array $dimensions): void
    {
        foreach ($this->modifiers as $modifier) {
            $modifier->modify($index_table, $dimensions);
        }
    }
}