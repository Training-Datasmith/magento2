<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager_Interface;
class Scope_Resolver implements Scope_Resolver_Interface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $object_manager;
    /**
     * @var ScopeInterface
     */
    private $default_scope;
    /**
     * ScopeResolver constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * {@inheritdoc}
     * @return ScopeDefault
     */
    public function get_scope($scope_id = null)
    {
        if (!$this->default_scope) {
            $this->default_scope = $this->object_manager->create(Scope_Default::class);
        }
        return $this->default_scope;
    }
    /**
     * Retrieve a list of available scopes
     *
     * @return ScopeInterface[]
     */
    public function get_scopes()
    {
        return [$this->default_scope];
    }
}