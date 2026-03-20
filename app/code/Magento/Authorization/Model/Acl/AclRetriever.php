<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Acl;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\Acl\Role\Current_Role_Context;
use Magento\Framework\Exception\Authorization_Exception;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Permission tree retriever
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Acl_Retriever
{
    public const PERMISSION_ANONYMOUS = 'anonymous';
    public const PERMISSION_SELF = 'self';
    /**
     * @var CurrentRoleContext
     */
    private $current_role_context;
    /**
     * Initialize dependencies.
     */
    public function __construct(protected \Magento\Framework\Acl\Builder $acl_builder, protected \Magento\Authorization\Model\Resource_Model\Role\Collection_Factory $role_collection_factory, protected \Magento\Authorization\Model\Resource_Model\Rules\Collection_Factory $rules_collection_factory, protected \Psr\Log\Logger_Interface $logger, ?Current_Role_Context $current_role_context = null)
    {
        $this->current_role_context = $current_role_context ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Current_Role_Context::class);
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
    public function get_allowed_resources_by_user($user_type, $user_id)
    {
        if ($user_type == User_Context_Interface::USER_TYPE_GUEST) {
            return [self::PERMISSION_ANONYMOUS];
        }
        if ($user_type == User_Context_Interface::USER_TYPE_CUSTOMER) {
            return [self::PERMISSION_SELF];
        }
        try {
            $role = $this->_get_user_role($user_type, $user_id);
            if (!$role) {
                throw new Authorization_Exception(__("The role wasn't found for the user. Verify the role and try again."));
            }
            $allowed_resources = $this->get_allowed_resources_by_role($role->get_id());
        } catch (Authorization_Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new Localized_Exception(__('Something went wrong while compiling a list of allowed resources. ' . 'You can find out more in the exceptions log.'));
        }
        return $allowed_resources;
    }
    /**
     * Get a list of available resource using user role id
     *
     * @param string $roleId
     * @return string[]
     */
    public function get_allowed_resources_by_role($role_id)
    {
        try {
            $allowed_resources = [];
            $rules_collection = $this->rules_collection_factory->create();
            $rules_collection->get_by_roles($role_id)->load();
            if ($role_id && (int) $role_id !== $this->current_role_context->get_role_id()) {
                $this->acl_builder->reset_runtime_acl();
            }
            $this->current_role_context->set_role_id((int) $role_id);
            $acl = $this->acl_builder->get_acl();
            /** @var \Magento\Authorization\Model\Rules $ruleItem */
            foreach ($rules_collection->get_items() as $rule_item) {
                $resource_id = $rule_item->get_resource_id();
                if ($acl->has_resource($resource_id) && $acl->is_allowed($role_id, $resource_id)) {
                    $allowed_resources[] = $resource_id;
                }
            }
        } finally {
            $this->current_role_context->_reset_state();
        }
        return $allowed_resources;
    }
    /**
     * Identify user role from user identifier.
     *
     * @param string $userType
     * @param int $userId
     * @return \Magento\Authorization\Model\Role|bool False if no role associated with provided user was found.
     * @throws \LogicException
     */
    protected function _get_user_role($user_type, $user_id)
    {
        if (!$this->_can_role_be_created_for_user_type($user_type)) {
            throw new \LogicException("The role with user type '{$user_type}' does not exist and cannot be created");
        }
        $role_collection = $this->role_collection_factory->create();
        /** @var Role $role */
        $role = $role_collection->set_user_filter($user_id, $user_type)->get_first_item();
        return $role->get_id() ? $role : false;
    }
    /**
     * Check if the role can be associated with user having provided user type.
     *
     * Roles can be created for integrations and admin users only.
     *
     * @param int $userType
     */
    protected function _can_role_be_created_for_user_type($user_type): bool
    {
        return $user_type == User_Context_Interface::USER_TYPE_INTEGRATION || $user_type == User_Context_Interface::USER_TYPE_ADMIN;
    }
}