<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Resource_Model\Role\Grid;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
/**
 * Admin role data grid collection
 */
class Collection extends \Magento\Authorization\Model\Resource_Model\Role\Collection
{
    /**
     * Prepare select for load
     *
     * @return $this
     */
    protected function _init_select(): static
    {
        parent::_init_select();
        $this->add_field_to_filter('role_type', Role_Group::ROLE_TYPE);
        return $this;
    }
}