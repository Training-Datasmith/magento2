<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy_Interface;
/**
 * Creates strategies using configuration
 */
class Factory
{
    /**
     * Default strategy for objects which implement Identity interface
     *
     * @var StrategyInterface
     */
    private $identifier_strategy;
    /**
     * Strategy for objects which don't implement Identity interface
     *
     * @var StrategyInterface
     */
    private $dummy_strategy;
    /**
     * Strategies map
     *
     * @var array
     */
    private $custom_strategies = [];
    /**
     * Factory constructor.
     *
     * @param Identifier $identifierStrategy
     * @param Dummy $dummyStrategy
     * @param array $customStrategies
     */
    public function __construct(\Magento\Framework\App\Cache\Tag\Strategy\Identifier $identifier_strategy, \Magento\Framework\App\Cache\Tag\Strategy\Dummy $dummy_strategy, $custom_strategies = [])
    {
        $this->custom_strategies = $custom_strategies;
        $this->identifier_strategy = $identifier_strategy;
        $this->dummy_strategy = $dummy_strategy;
    }
    /**
     * Return tag strategy for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\Cache\Tag\StrategyInterface
     */
    public function get_strategy($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }
        $class_hierarchy = array_merge([get_class($object) => get_class($object)], class_parents($object), class_implements($object));
        $result = array_intersect(array_keys($this->custom_strategies), $class_hierarchy);
        if ($result) {
            return $this->custom_strategies[array_shift($result)];
        }
        if ($object instanceof \Magento\Framework\Data_Object\Identity_Interface) {
            return $this->identifier_strategy;
        }
        return $this->dummy_strategy;
    }
}