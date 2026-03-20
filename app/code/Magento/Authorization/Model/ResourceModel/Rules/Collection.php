<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Resource_Model\Rules;

/**
 * Rules collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorization\Model\Rules::class, \Magento\Authorization\Model\Resource_Model\Rules::class);
    }
    /**
     * Get rules by role id
     *
     * @param int $roleId
     * @return $this
     */
    public function get_by_roles($role_id): static
    {
        $this->add_field_to_filter('role_id', (int) $role_id);
        return $this;
    }
    /**
     * Sort by length
     *
     * @return $this
     */
    public function add_sort_by_length(): static
    {
        $length = $this->get_connection()->get_length_sql('{{resource_id}}');
        $this->add_expression_field_to_select('length', $length, 'resource_id');
        $this->get_select()->order('length ' . \Magento\Framework\DB\Select::SQL_DESC);
        return $this;
    }
}