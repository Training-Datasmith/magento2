<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Setup\Patch\Data;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\Setup\Module_Data_Setup_Interface;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
use Magento\Framework\Setup\Patch\Patch_Version_Interface;
/**
 * Class InitializeAuthRoles
 * @package Magento\Authorization\Setup\Patch
 */
class Initialize_Auth_Roles implements Data_Patch_Interface, Patch_Version_Interface
{
    /**
     * InitializeAuthRoles constructor.
     */
    public function __construct(private readonly Module_Data_Setup_Interface $module_data_setup, private readonly \Magento\Authorization\Setup\Authorization_Factory $auth_factory)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $role_collection = $this->auth_factory->create_role_collection()->add_field_to_filter('parent_id', 0)->add_field_to_filter('tree_level', 1)->add_field_to_filter('role_type', Role_Group::ROLE_TYPE)->add_field_to_filter('user_id', 0)->add_field_to_filter('user_type', User_Context_Interface::USER_TYPE_ADMIN)->add_field_to_filter('role_name', 'Administrators');
        if ($role_collection->count() == 0) {
            $adm_group_role = $this->auth_factory->create_role()->set_data(['parent_id' => 0, 'tree_level' => 1, 'sort_order' => 1, 'role_type' => Role_Group::ROLE_TYPE, 'user_id' => 0, 'user_type' => User_Context_Interface::USER_TYPE_ADMIN, 'role_name' => 'Administrators'])->save();
        } else {
            /** @var \Magento\Authorization\Model\ResourceModel\Role $item */
            foreach ($role_collection as $item) {
                $adm_group_role = $item;
                break;
            }
        }
        $rules_collection = $this->auth_factory->create_rules_collection()->add_field_to_filter('role_id', $adm_group_role->get_id())->add_field_to_filter('resource_id', 'all');
        if ($rules_collection->count() == 0) {
            $this->auth_factory->create_rules()->set_data(['role_id' => $adm_group_role->get_id(), 'resource_id' => 'Magento_Backend::all', 'privileges' => null, 'permission' => 'allow'])->save();
        } else {
            /** @var \Magento\Authorization\Model\Rules $rule */
            foreach ($rules_collection as $rule) {
                $rule->set_data('resource_id', 'Magento_Backend::all')->save();
            }
        }
        /**
         * Delete rows by condition from authorization_rule
         */
        $table_name = $this->module_data_setup->get_table('authorization_rule');
        if ($table_name) {
            $this->module_data_setup->get_connection()->delete($table_name, ['resource_id = ?' => 'admin/system/tools/compiler']);
        }
    }
    /**
     * {@inheritdoc}
     */
    public static function get_dependencies(): array
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public static function get_version(): string
    {
        return '2.0.0';
    }
    /**
     * {@inheritdoc}
     */
    public function get_aliases(): array
    {
        return [];
    }
}