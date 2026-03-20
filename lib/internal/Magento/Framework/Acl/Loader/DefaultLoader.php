<?php

declare (strict_types=1);
/**
 * Default acl loader. Used as a fallback when no loaders were defined. Doesn't change ACL object passed.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Loader;

class Default_Loader implements \Magento\Framework\Acl\Loader_Interface
{
    /**
     * Don't do anything to acl object.
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populate_acl(\Magento\Framework\Acl $acl)
    {
        // Do nothing
    }
}