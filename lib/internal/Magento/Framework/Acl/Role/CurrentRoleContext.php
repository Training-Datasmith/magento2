<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Acl\Role;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Holds the current role id during ACL building within a single request.
 */
class Current_Role_Context implements Reset_After_Request_Interface
{
    /**
     * @var int|null
     */
    private $role_id = null;
    /**
     * Set the current role ID.
     *
     * @param int|null $roleId
     * @return void
     */
    public function set_role_id(int|null $role_id): void
    {
        $this->role_id = $role_id;
    }
    /**
     * Get the current role ID.
     *
     * @return int|null
     */
    public function get_role_id(): int|null
    {
        return $this->role_id;
    }
    /**
     * Reset the state after a request.
     *
     * @return void
     */
    public function _reset_state(): void
    {
        $this->role_id = null;
    }
}