<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Invalidation tags generator
 *
 * @api
 * @since 100.1.3
 */
interface Strategy_Interface
{
    /**
     * Return invalidation tags for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     * @since 100.1.3
     */
    public function get_tags($object);
}