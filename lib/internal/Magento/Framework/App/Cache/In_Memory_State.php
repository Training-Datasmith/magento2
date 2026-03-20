<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

/**
 * In memory cache state
 *
 * Used to ease testing of cache state modifications
 */
class In_Memory_State implements State_Interface
{
    /** @var bool[] */
    private $runtime_state = [];
    /** @var bool[] */
    private $persisted_state = [];
    /**
     * InMemoryState constructor.
     * @param array $persistedState
     */
    public function __construct(array $persisted_state = [])
    {
        $this->persisted_state = $persisted_state;
    }
    /**
     * @inheritDoc
     */
    public function is_enabled($cache_type)
    {
        return $this->runtime_state[$cache_type] ?? $this->persisted_state[$cache_type] ?? false;
    }
    /**
     * @inheritDoc
     */
    public function set_enabled($cache_type, $is_enabled)
    {
        $this->runtime_state[$cache_type] = $is_enabled;
    }
    /**
     * @inheritDoc
     */
    public function persist()
    {
        $this->persisted_state = $this->runtime_state + $this->persisted_state;
        $this->runtime_state = [];
    }
    /**
     * Creates new instance with persistent state updated values
     *
     * @param bool[] $state
     * @return self
     */
    public function with_persisted_state(array $state): self
    {
        $new_state = new self();
        $new_state->persisted_state = $state + $this->persisted_state;
        return $new_state;
    }
}