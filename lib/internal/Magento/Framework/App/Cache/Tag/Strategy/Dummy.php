<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy_Interface;
/**
 * Always return empty tags array
 */
class Dummy implements Strategy_Interface
{
    /**
     * {@inheritdoc}
     */
    public function get_tags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }
        return [];
    }
}