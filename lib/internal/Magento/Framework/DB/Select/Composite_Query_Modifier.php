<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
/**
 * Apply multiple query modifiers to select
 */
class Composite_Query_Modifier implements Query_Modifier_Interface
{
    /**
     * @var QueryModifierInterface[]
     */
    private $query_modifiers;
    /**
     * Constructor
     *
     * @param QueryModifierInterface[] $queryModifiers
     */
    public function __construct(array $query_modifiers = [])
    {
        $this->query_modifiers = $query_modifiers;
    }
    /**
     * {@inheritdoc}
     */
    public function modify(Select $select)
    {
        foreach ($this->query_modifiers as $modifier) {
            $modifier->modify($select);
        }
    }
}