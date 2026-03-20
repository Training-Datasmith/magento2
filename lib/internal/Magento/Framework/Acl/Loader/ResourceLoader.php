<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Loader;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException as AclInvalidArgumentException;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Acl_Resource;
use Magento\Framework\Acl\Acl_Resource\Provider_Interface;
use Magento\Framework\Acl\Acl_Resource_Factory;
/**
 * ACL Resource Loader
 */
class Resource_Loader implements \Magento\Framework\Acl\Loader_Interface
{
    /**
     * Acl resource config
     *
     * @var ProviderInterface
     */
    protected $_resource_provider;
    /**
     * @var AclResourceFactory
     */
    protected $_resource_factory;
    /**
     * @param ProviderInterface $resourceProvider
     * @param AclResourceFactory $resourceFactory
     */
    public function __construct(Provider_Interface $resource_provider, Acl_Resource_Factory $resource_factory)
    {
        $this->_resource_provider = $resource_provider;
        $this->_resource_factory = $resource_factory;
    }
    /**
     * Populate ACL with resources from external storage
     *
     * @param Acl $acl
     * @return void
     * @throws AclInvalidArgumentException
     */
    public function populate_acl(Acl $acl)
    {
        $this->_add_resource_tree($acl, $this->_resource_provider->get_acl_resources(), null);
    }
    /**
     * Add list of nodes and their children to acl
     *
     * @param Acl $acl
     * @param array $resources
     * @param AclResource $parent
     * @return void
     * @throws \InvalidArgumentException
     * @throws AclInvalidArgumentException
     */
    protected function _add_resource_tree(Acl $acl, array $resources, ?Acl_Resource $parent = null)
    {
        foreach ($resources as $resource_config) {
            if (!isset($resource_config['id'])) {
                throw new \InvalidArgumentException('Missing ACL resource identifier');
            }
            $resource = $this->_resource_factory->create_resource(['resourceId' => $resource_config['id']]);
            $acl->add_resource($resource, $parent);
            if (isset($resource_config['children'])) {
                $this->_add_resource_tree($acl, $resource_config['children'], $resource);
            }
        }
    }
}