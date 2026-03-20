<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Access Control List Builder. Retrieves required role/rule/resource loaders
 * and uses them to populate provided ACL object. Acl object is put to cache after creation.
 * On consequent requests, ACL object is deserialized from cache.
 *
 * @api
 * @since 100.0.2
 */
class Builder implements Reset_After_Request_Interface
{
    /**
     * Acl object
     *
     * @var \Magento\Framework\Acl
     */
    protected $_acl;
    /**
     * Acl loader list
     *
     * @var \Magento\Framework\Acl\LoaderInterface[]
     */
    protected $_loader_pool;
    /**
     * @var \Magento\Framework\AclFactory
     */
    protected $_acl_factory;
    /**
     * @param \Magento\Framework\AclFactory $aclFactory
     * @param \Magento\Framework\Acl\LoaderInterface $roleLoader
     * @param \Magento\Framework\Acl\LoaderInterface $resourceLoader
     * @param \Magento\Framework\Acl\LoaderInterface $ruleLoader
     */
    public function __construct(\Magento\Framework\Acl_Factory $acl_factory, \Magento\Framework\Acl\Loader_Interface $role_loader, \Magento\Framework\Acl\Loader_Interface $resource_loader, \Magento\Framework\Acl\Loader_Interface $rule_loader)
    {
        $this->_acl_factory = $acl_factory;
        $this->_loader_pool = [$role_loader, $resource_loader, $rule_loader];
    }
    /**
     * Build Access Control List
     *
     * @return \Magento\Framework\Acl
     * @throws \LogicException
     */
    public function get_acl()
    {
        if ($this->_acl instanceof \Magento\Framework\Acl) {
            return $this->_acl;
        }
        try {
            $this->_acl = $this->_acl_factory->create();
            foreach ($this->_loader_pool as $loader) {
                $loader->populate_acl($this->_acl);
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not create an acl object: ' . $e->get_message());
        }
        return $this->_acl;
    }
    /**
     * Remove cached ACL instance.
     *
     * @return $this
     * @since 101.0.0
     */
    public function reset_runtime_acl()
    {
        $this->_acl = null;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function _reset_state(): void
    {
        $this->reset_runtime_acl();
    }
}