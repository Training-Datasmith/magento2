<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\GroupFactory;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\Acl\Role\UserFactory;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\LoaderInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Acl Role Loader
 */
class Role implements LoaderInterface
{
    /**
     * Cache key for ACL roles cache
     */
    public const ACL_ROLES_CACHE_KEY = 'authorization_role_cached_data';

    /**
     * @var GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var UserFactory
     */
    protected $_roleFactory;

    /**
     * @param string $cacheKey
     */
    public function __construct(
        GroupFactory $groupFactory,
        UserFactory $roleFactory,
        protected \Magento\Framework\App\ResourceConnection $_resource,
        private readonly CacheInterface $aclDataCache,
        private readonly Json $serializer,
        private $cacheKey = self::ACL_ROLES_CACHE_KEY
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_roleFactory = $roleFactory;
    }

    /**
     * Populate ACL with roles from external storage
     */
    public function populateAcl(\Magento\Framework\Acl $acl): void
    {
        foreach ($this->getRolesArray() as $role) {
            $parent = $role['parent_id'] > 0 ? $role['parent_id'] : null;
            switch ($role['role_type']) {
                case RoleGroup::ROLE_TYPE:
                    $acl->addRole($this->_groupFactory->create(['roleId' => $role['role_id']]), $parent);
                    break;

                case RoleUser::ROLE_TYPE:
                    if (!$acl->hasRole($role['role_id'])) {
                        $acl->addRole($this->_roleFactory->create(['roleId' => $role['role_id']]), $parent);
                    } else {
                        $acl->addRoleParent($role['role_id'], $parent);
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
    private function getRolesArray()
    {
        $rolesCachedData = $this->aclDataCache->load($this->cacheKey);
        if ($rolesCachedData) {
            return $this->serializer->unserialize($rolesCachedData);
        }

        $roleTableName = $this->_resource->getTableName('authorization_role');
        $connection = $this->_resource->getConnection();

        $select = $connection->select()
            ->from($roleTableName)
            ->order('tree_level');

        $rolesArray = $connection->fetchAll($select);
        $this->aclDataCache->save($this->serializer->serialize($rolesArray), $this->cacheKey);
        return $rolesArray;
    }
}
