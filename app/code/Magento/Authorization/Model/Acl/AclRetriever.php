<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Authorization\Model\Acl;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\Role\CurrentRoleContext;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Permission tree retriever
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AclRetriever
{
    public const PERMISSION_ANONYMOUS = 'anonymous';
    public const PERMISSION_SELF = 'self';

    /**
     * @var CurrentRoleContext
     */
    private $currentRoleContext;

    /**
     * Initialize dependencies.
     */
    public function __construct(
        protected \Magento\Framework\Acl\Builder $aclBuilder,
        protected \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        protected \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        protected \Psr\Log\LoggerInterface $logger,
        ?CurrentRoleContext $currentRoleContext = null
    ) {
        $this->currentRoleContext = $currentRoleContext ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(CurrentRoleContext::class);
    }

    /**
     * Get a list of available resources using user details
     *
     * @param string $userType
     * @param int $userId
     * @return string[]
     * @throws AuthorizationException
     * @throws LocalizedException
     */
    public function getAllowedResourcesByUser($userType, $userId)
    {
        if ($userType == UserContextInterface::USER_TYPE_GUEST) {
            return [self::PERMISSION_ANONYMOUS];
        }
        if ($userType == UserContextInterface::USER_TYPE_CUSTOMER) {
            return [self::PERMISSION_SELF];
        }
        try {
            $role = $this->_getUserRole($userType, $userId);
            if (!$role) {
                throw new AuthorizationException(
                    __("The role wasn't found for the user. Verify the role and try again.")
                );
            }
            $allowedResources = $this->getAllowedResourcesByRole($role->getId());
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __(
                    'Something went wrong while compiling a list of allowed resources. '
                    . 'You can find out more in the exceptions log.'
                )
            );
        }
        return $allowedResources;
    }

    /**
     * Get a list of available resource using user role id
     *
     * @param string $roleId
     * @return string[]
     */
    public function getAllowedResourcesByRole($roleId)
    {
        try {
            $allowedResources = [];
            $rulesCollection = $this->rulesCollectionFactory->create();
            $rulesCollection->getByRoles($roleId)->load();
            if ($roleId && (int) $roleId !== $this->currentRoleContext->getRoleId()) {
                $this->aclBuilder->resetRuntimeAcl();
            }
            $this->currentRoleContext->setRoleId((int) $roleId);
            $acl = $this->aclBuilder->getAcl();
            /** @var \Magento\Authorization\Model\Rules $ruleItem */
            foreach ($rulesCollection->getItems() as $ruleItem) {
                $resourceId = $ruleItem->getResourceId();
                if ($acl->hasResource($resourceId) && $acl->isAllowed($roleId, $resourceId)) {
                    $allowedResources[] = $resourceId;
                }
            }
        } finally {
            $this->currentRoleContext->_resetState();
        }

        return $allowedResources;
    }

    /**
     * Identify user role from user identifier.
     *
     * @param string $userType
     * @param int $userId
     * @return \Magento\Authorization\Model\Role|bool False if no role associated with provided user was found.
     * @throws \LogicException
     */
    protected function _getUserRole($userType, $userId)
    {
        if (!$this->_canRoleBeCreatedForUserType($userType)) {
            throw new \LogicException(
                "The role with user type '{$userType}' does not exist and cannot be created"
            );
        }
        $roleCollection = $this->roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();
        return $role->getId() ? $role : false;
    }

    /**
     * Check if the role can be associated with user having provided user type.
     *
     * Roles can be created for integrations and admin users only.
     *
     * @param int $userType
     */
    protected function _canRoleBeCreatedForUserType($userType): bool
    {
        return ($userType == UserContextInterface::USER_TYPE_INTEGRATION)
            || ($userType == UserContextInterface::USER_TYPE_ADMIN);
    }
}
