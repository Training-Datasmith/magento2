<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Asset\Materialization_Strategy;

use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\View\Asset;
class Factory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Strategies list
     *
     * @var array
     */
    protected $strategies_list;
    /**
     * Default strategy key
     */
    public const DEFAULT_STRATEGY = \Magento\Framework\App\View\Asset\Materialization_Strategy\Copy::class;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param StrategyInterface[] $strategiesList
     */
    public function __construct(Object_Manager_Interface $object_manager, $strategies_list = [])
    {
        $this->object_manager = $object_manager;
        $this->strategies_list = $strategies_list;
    }
    /**
     * Create materialization strategy basing on asset
     *
     * @param Asset\LocalInterface $asset
     * @return StrategyInterface
     *
     * @throws \LogicException
     */
    public function create(Asset\Local_Interface $asset)
    {
        if (empty($this->strategies_list)) {
            $this->strategies_list[] = $this->object_manager->get(self::DEFAULT_STRATEGY);
        }
        foreach ($this->strategies_list as $strategy) {
            if ($strategy->is_supported($asset)) {
                return $strategy;
            }
        }
        throw new \LogicException('No materialization strategy is supported');
    }
}