<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Framework\Acl;
use Magento\Framework\Acl\Data\Cache_Interface;
use Magento\Framework\Acl\Loader_Interface;
use Magento\Framework\Acl\Role\Current_Role_Context;
use Magento\Framework\Acl\Root_Resource;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Acl Rule Loader
 */
class Rule implements Loader_Interface
{
    /**
     * Rules array cache key
     */
    public const ACL_RULE_CACHE_KEY = 'authorization_rule_cached_data';
    /**
     * Allow everything resource id
     */
    private const ALLOW_EVERYTHING = 'Magento_Backend::all';
    private readonly string $cache_key;
    /**
     * @var CurrentRoleContext
     */
    private $role_context;
    /**
     * @param array $data
     * @param string $cacheKey
     * @SuppressWarnings(PHPMD.UnusedFormalParameter):
     */
    public function __construct(private readonly Root_Resource $_root_resource, protected \Magento\Framework\App\Resource_Connection $_resource, private readonly Cache_Interface $acl_data_cache, private readonly Json $serializer, ?array $data = [], ?string $cache_key = self::ACL_RULE_CACHE_KEY, ?Current_Role_Context $role_context = null)
    {
        $this->cache_key = $cache_key ?? self::ACL_RULE_CACHE_KEY;
        $this->role_context = $role_context ?? \Magento\Framework\App\Object_Manager::get_instance()->get(Current_Role_Context::class);
    }
    /**
     * Populate ACL with rules from external storage
     */
    public function populate_acl(Acl $acl): void
    {
        $role_id = $this->role_context->get_role_id();
        $result = $role_id ? $this->apply_permissions_for_role($acl, (int) $role_id) : $this->apply_permissions_according_to_rules($acl);
        $this->deny_permissions_for_missing_rules($acl, $result);
    }
    /**
     * Apply permissions for a specific role
     */
    private function apply_permissions_for_role(Acl $acl, int $role_id): array
    {
        $applied_role_permissions_per_resource = [];
        foreach ($this->get_rules_array_for_role($role_id) as $rule) {
            $applied_role_permissions_per_resource = $this->get_applied_role_permissions_per_resource($rule, $acl, $applied_role_permissions_per_resource);
        }
        return $applied_role_permissions_per_resource;
    }
    /**
     * Apply ACL with rules
     *
     * @return array[]
     */
    private function apply_permissions_according_to_rules(Acl $acl): array
    {
        $applied_role_permissions_per_resource = [];
        foreach ($this->get_rules_array() as $rule) {
            $applied_role_permissions_per_resource = $this->get_applied_role_permissions_per_resource($rule, $acl, $applied_role_permissions_per_resource);
        }
        return $applied_role_permissions_per_resource;
    }
    /**
     * Deny permissions for missing rules
     *
     * For all rules that were not regenerated in authorization_rule table,
     * when adding a new module and without re-saving all roles,
     * consider not present rules with deny permissions
     */
    private function deny_permissions_for_missing_rules(Acl $acl, array $applied_role_permissions_per_resource): void
    {
        $consolidated_denied_role_ids = array_unique(array_merge(...array_column($applied_role_permissions_per_resource, 'deny')));
        $has_applied_permissions = count($applied_role_permissions_per_resource) > 0;
        $has_denied_roles = count($consolidated_denied_role_ids) > 0;
        $all_allowed = count($applied_role_permissions_per_resource) === 1 && isset($applied_role_permissions_per_resource[static::ALLOW_EVERYTHING]);
        if ($has_applied_permissions && $has_denied_roles && !$all_allowed) {
            // Add the resources that are not present in the rules at all,
            // assuming that they must be denied for all roles by default
            $resources_undefined_in_authorization_rules = array_diff($acl->get_resources(), array_keys($applied_role_permissions_per_resource));
            $assume_denied_role_list_per_resource = array_fill_keys($resources_undefined_in_authorization_rules, $consolidated_denied_role_ids);
            // Add the resources that are permitted for one role and not present in others at all,
            // assuming that they must be denied for all other roles by default
            foreach ($applied_role_permissions_per_resource as $resource => $permissions) {
                $allowed_roles = $permissions['allow'];
                $denied_roles = $permissions['deny'];
                $assumed_denied_roles = array_diff($consolidated_denied_role_ids, $allowed_roles, $denied_roles);
                if ($assumed_denied_roles) {
                    $assume_denied_role_list_per_resource[$resource] = $assumed_denied_roles;
                }
            }
            // Deny permissions for missing rules
            foreach ($assume_denied_role_list_per_resource as $resource => $deny_roles) {
                $acl->deny($deny_roles, $resource, null);
            }
        }
    }
    /**
     * Get application ACL rules array.
     *
     * @return array
     */
    private function get_rules_array()
    {
        $rules_cached_data = $this->acl_data_cache->load($this->cache_key);
        if ($rules_cached_data) {
            return $this->serializer->unserialize($rules_cached_data);
        }
        $rule_table = $this->_resource->get_table_name('authorization_rule');
        $connection = $this->_resource->get_connection();
        $select = $connection->select()->from(['r' => $rule_table]);
        $rules_arr = $connection->fetch_all($select);
        $this->acl_data_cache->save($this->serializer->serialize($rules_arr), $this->cache_key);
        return $rules_arr;
    }
    /**
     * Get application ACL rules array for a specific role.
     */
    private function get_rules_array_for_role(int $role_id): array
    {
        $group_role_id = $this->resolve_group_role_id($role_id);
        $cache_key = hash('sha256', self::ACL_RULE_CACHE_KEY . '_' . $group_role_id);
        $rules_cached_data = $this->acl_data_cache->load($cache_key);
        if ($rules_cached_data) {
            return $this->serializer->unserialize($rules_cached_data);
        }
        $rule_table = $this->_resource->get_table_name('authorization_rule');
        $connection = $this->_resource->get_connection();
        $select = $connection->select()->from(['r' => $rule_table])->where('role_id = ?', $group_role_id)->order('rule_id ASC');
        $rules_arr = $connection->fetch_all($select);
        $this->acl_data_cache->save($this->serializer->serialize($rules_arr), $cache_key);
        return $rules_arr;
    }
    /**
     * Resolve the group role id for a given role id
     */
    private function resolve_group_role_id(int $role_id): int
    {
        $role_table = $this->_resource->get_table_name('authorization_role');
        $connection = $this->_resource->get_connection();
        $select = $connection->select()->from($role_table, ['role_type', 'parent_id'])->where('role_id = ?', $role_id)->limit(1);
        $row = $connection->fetch_row($select);
        if (is_array($row) && isset($row['role_type']) && $row['role_type'] === 'U' && (int) ($row['parent_id'] ?? 0) > 0) {
            return (int) $row['parent_id'];
        }
        return $role_id;
    }
    /**
     * Apply rule to ACL and return applied permissions per resource
     */
    private function get_applied_role_permissions_per_resource(array $rule, Acl $acl, array $applied_role_permissions_per_resource): array
    {
        $role = $rule['role_id'];
        $resource = $rule['resource_id'];
        $privileges = !empty($rule['privileges']) ? explode(',', (string) $rule['privileges']) : null;
        if ($acl->has_resource($resource)) {
            $applied_role_permissions_per_resource[$resource]['allow'] ??= [];
            $applied_role_permissions_per_resource[$resource]['deny'] ??= [];
            if ($rule['permission'] == 'allow') {
                if ($resource === $this->_root_resource->get_id()) {
                    $acl->allow($role, null, $privileges);
                }
                $acl->allow($role, $resource, $privileges);
                $applied_role_permissions_per_resource[$resource]['allow'][] = $role;
            } elseif ($rule['permission'] == 'deny') {
                $acl->deny($role, $resource, $privileges);
                $applied_role_permissions_per_resource[$resource]['deny'][] = $role;
            }
        }
        return $applied_role_permissions_per_resource;
    }
}