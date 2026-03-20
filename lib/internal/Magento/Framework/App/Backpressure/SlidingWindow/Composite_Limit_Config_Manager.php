<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\Exception\RuntimeException;
/**
 * Delegates finding configs for different requests types to other instances
 */
class Composite_Limit_Config_Manager implements Limit_Config_Manager_Interface
{
    /**
     * @var LimitConfigManagerInterface[]
     */
    private array $configs;
    /**
     * @param LimitConfigManagerInterface[] $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }
    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function read_limit(Context_Interface $context): Limit_Config
    {
        if (isset($this->configs[$context->get_type_id()])) {
            return $this->configs[$context->get_type_id()]->read_limit($context);
        }
        throw new RuntimeException(__('Failed to find config manager for "%typeId".', ['typeId' => $context->get_type_id()]));
    }
}