<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\Group_Factory;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\Acl\Role\User_Factory;
use Magento\Framework\Acl\Data\Cache_Interface;
use Magento\Framework\Acl\Loader_Interface;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Acl Role Loader
 */
class Role implements Loader_Interface
{
    /**
     * Cache key for ACL roles cache
     */
    public const ACL_ROLES_CACHE_KEY = 'authorization_role_cached_data';
    /**
     * @var GroupFactory
     */
    protected $_group_factory;
    /**
     * @var UserFactory
     */
    protected $_role_factory;
    /**
     * @param string $cacheKey
     */
    public function __construct(Group_Factory $group_factory, User_Factory $role_factory, protected \Magento\Framework\App\Resource_Connection $_resource, private readonly Cache_Interface $acl_data_cache, private readonly Json $serializer, private $cache_key = self::ACL_ROLES_CACHE_KEY)
    {
        $this->_group_factory = $group_factory;
        $this->_role_factory = $role_factory;
    }
    /**
     * Populate ACL with roles from external storage
     */
    public function populate_acl(\Magento\Framework\Acl $acl): void
    {
        foreach ($this->get_roles_array() as $role) {
            $parent = $role['parent_id'] > 0 ? $role['parent_id'] : null;
            switch ($role['role_type']) {
                case Role_Group::ROLE_TYPE:
                    $acl->add_role($this->_group_factory->create(['roleId' => $role['role_id']]), $parent);
                    break;
                case Role_User::ROLE_TYPE:
                    if (!$acl->has_role($role['role_id'])) {
                        $acl->add_role($this->_role_factory->create(['roleId' => $role['role_id']]), $parent);
                    } else {
                        $acl->add_role_parent($role['role_id'], $parent);
                    }
                    break;
            }
        }
    }
    /**
     * Get application ACL roles array
     *
     * @return array
     */
    private function get_roles_array()
    {
        $roles_cached_data = $this->acl_data_cache->load($this->cache_key);
        if ($roles_cached_data) {
            return $this->serializer->unserialize($roles_cached_data);
        }
        $role_table_name = $this->_resource->get_table_name('authorization_role');
        $connection = $this->_resource->get_connection();
        $select = $connection->select()->from($role_table_name)->order('tree_level');
        $roles_array = $connection->fetch_all($select);
        $this->acl_data_cache->save($this->serializer->serialize($roles_array), $this->cache_key);
        return $roles_array;
    }
}