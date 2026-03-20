<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy_Interface;
/**
 * Produce cache tags using IdentityInterface
 */
class Identifier implements Strategy_Interface
{
    /**
     * {@inheritdoc}
     */
    public function get_tags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }
        if ($object instanceof \Magento\Framework\Data_Object\Identity_Interface) {
            return $object->get_identities();
        }
        return [];
    }
}