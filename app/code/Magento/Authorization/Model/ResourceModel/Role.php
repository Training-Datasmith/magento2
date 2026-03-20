<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Resource_Model;

use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Framework\Cache\Cache_Constants;
/**
 * Admin role resource model
 */
class Role extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * Authorization rule table name
     *
     * @var string
     */
    protected $_rule_table;
    /**
     * Cache frontend instance
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;
    /**
     * @param string $connectionName
     */
    public function __construct(\Magento\Framework\Model\Resource_Model\Db\Context $context, \Magento\Framework\App\Cache_Interface $cache, $connection_name = null)
    {
        parent::__construct($context, $connection_name);
        $this->_cache = $cache->get_frontend();
    }
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorization_role', 'role_id');
        $this->_rule_table = $this->get_table('authorization_rule');
    }
    /**
     * Process role before saving
     *
     * @return $this
     */
    protected function _before_save(\Magento\Framework\Model\Abstract_Model $role): static
    {
        if ($role->get_id() == '') {
            if ($role->get_id_field_name()) {
                $role->unset_data($role->get_id_field_name());
            } else {
                $role->unset_data('id');
            }
        }
        if (!$role->get_tree_level()) {
            $tree_level = 0;
            if ($role->get_pid() > 0) {
                $select = $this->get_connection()->select()->from($this->get_main_table(), ['tree_level'])->where("{$this->get_id_field_name()} = :pid");
                $binds = ['pid' => (int) $role->get_pid()];
                $tree_level = $this->get_connection()->fetch_one($select, $binds);
            }
            $role->set_tree_level($tree_level + 1);
        }
        if ($role->get_name()) {
            $role->set_role_name($role->get_name());
        }
        return $this;
    }
    /**
     * Process role after saving
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _after_save(\Magento\Framework\Model\Abstract_Model $role): static
    {
        $this->_cache->clean(Cache_Constants::CLEANING_MODE_MATCHING_TAG, [\Magento\Backend\Block\Menu::CACHE_TAGS]);
        return $this;
    }
    /**
     * Process role after deleting
     *
     * @return $this
     */
    protected function _after_delete(\Magento\Framework\Model\Abstract_Model $role): static
    {
        $connection = $this->get_connection();
        $connection->delete($this->get_main_table(), ['parent_id = ?' => (int) $role->get_id()]);
        $connection->delete($this->_rule_table, ['role_id = ?' => (int) $role->get_id()]);
        $this->_cache->clean(Cache_Constants::CLEANING_MODE_MATCHING_TAG, [\Magento\Backend\Block\Menu::CACHE_TAGS]);
        return $this;
    }
    /**
     * Get role users
     *
     * @return array
     */
    public function get_role_users(\Magento\Authorization\Model\Role $role)
    {
        $connection = $this->get_connection();
        $binds = ['role_id' => $role->get_id(), 'role_type' => Role_User::ROLE_TYPE];
        $select = $connection->select()->from($this->get_main_table(), ['user_id'])->where('parent_id = :role_id')->where('role_type = :role_type')->where('user_id > 0');
        return $connection->fetch_col($select, $binds);
    }
}