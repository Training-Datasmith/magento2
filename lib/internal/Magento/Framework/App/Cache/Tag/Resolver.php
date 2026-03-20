<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Resolves invalidation tags for specified object using different strategies
 */
class Resolver
{
    /**
     * Tag strategies factory
     *
     * @var Strategy\Factory
     */
    private $strategy_factory;
    /**
     * Resolver constructor.
     *
     * @param Strategy\Factory $factory
     */
    public function __construct(\Magento\Framework\App\Cache\Tag\Strategy\Factory $factory)
    {
        $this->strategy_factory = $factory;
    }
    /**
     * Identify invalidation tags for the object using custom strategies
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     */
    public function get_tags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }
        return $this->strategy_factory->get_strategy($object)->get_tags($object);
    }
}