<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model;

use Magento\Authorization\Model\Resource_Model\Role\Collection;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Model\Abstract_Model;
/**
 * Admin Role Model
 *
 * @method int getParentId()
 * @method Role setParentId(int $value)
 * @method int getTreeLevel()
 * @method Role setTreeLevel(int $value)
 * @method int getSortOrder()
 * @method Role setSortOrder(int $value)
 * @method string getRoleType()
 * @method Role setRoleType(string $value)
 * @method int getUserId()
 * @method Role setUserId(int $value)
 * @method string getUserType()
 * @method Role setUserType(string $value)
 * @method string getRoleName()
 * @method Role setRoleName(string $value)
 * @api
 * @since 100.0.2
 */
class Role extends Abstract_Model
{
    /**
     * @var string
     */
    protected $_event_prefix = 'authorization_roles';
    /**
     * @var string
     */
    protected $_cache_tag = 'user_assigned_role';
    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_diff($properties, ['_resource', '_resourceCollection']);
    }
    /**
     * @inheritDoc
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $object_manager = Object_Manager::get_instance();
        $this->_resource = $object_manager->get(Resource_Model\Role::class);
        $this->_resource_collection = $object_manager->get(Collection::class);
    }
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Resource_Model\Role::class);
    }
    /**
     * Obsolete method of update
     *
     * @return $this
     * @deprecated Method was never implemented and used.
     */
    public function update(): static
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        trigger_error('Method was never implemented and used.', E_USER_DEPRECATED);
        return $this;
    }
    /**
     * Return users for role
     *
     * @return array
     */
    public function get_role_users()
    {
        return $this->get_resource()->get_role_users($this);
    }
}