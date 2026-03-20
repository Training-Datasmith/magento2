<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Setup;

/**
 * Resource Setup Model
 *
 * @codeCoverageIgnore
 */
class Authorization_Factory
{
    /**
     * Role model factory
     *
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_role_collection_factory;
    /**
     * Factory for rules model
     *
     * @var \Magento\Authorization\Model\RulesFactory
     */
    protected $_rules_collection_factory;
    /**
     * Role model factory
     *
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_role_factory;
    /**
     * Rules model factory
     *
     * @var \Magento\Authorization\Model\RulesFactory
     */
    protected $_rules_factory;
    /**
     * Init
     */
    public function __construct(\Magento\Authorization\Model\Resource_Model\Role\Collection_Factory $role_collection_factory, \Magento\Authorization\Model\Resource_Model\Rules\Collection_Factory $rules_collection_factory, \Magento\Authorization\Model\Role_Factory $role_factory, \Magento\Authorization\Model\Rules_Factory $rules_factory)
    {
        $this->_role_collection_factory = $role_collection_factory;
        $this->_rules_collection_factory = $rules_collection_factory;
        $this->_role_factory = $role_factory;
        $this->_rules_factory = $rules_factory;
    }
    /**
     * Creates role collection
     *
     * @return \Magento\Authorization\Model\ResourceModel\Role\Collection
     */
    public function create_role_collection()
    {
        return $this->_role_collection_factory->create();
    }
    /**
     * Creates rules collection
     *
     * @return \Magento\Authorization\Model\ResourceModel\Rules\Collection
     */
    public function create_rules_collection()
    {
        return $this->_rules_collection_factory->create();
    }
    /**
     * Creates role model
     *
     * @return \Magento\Authorization\Model\Role
     */
    public function create_role()
    {
        return $this->_role_factory->create();
    }
    /**
     * Creates rules model
     *
     * @return \Magento\Authorization\Model\Rules
     */
    public function create_rules()
    {
        return $this->_rules_factory->create();
    }
}