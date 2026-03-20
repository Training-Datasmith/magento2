<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model;

/**
 * Admin Rules Model
 *
 * @method int getRoleId()
 * @method \Magento\Authorization\Model\Rules setRoleId(int $value)
 * @method string getResourceId()
 * @method \Magento\Authorization\Model\Rules setResourceId(string $value)
 * @method string getPrivileges()
 * @method \Magento\Authorization\Model\Rules setPrivileges(string $value)
 * @method int getAssertId()
 * @method \Magento\Authorization\Model\Rules setAssertId(int $value)
 * @method string getPermission()
 * @method \Magento\Authorization\Model\Rules setPermission(string $value)
 * @api
 * @since 100.0.2
 */
class Rules extends \Magento\Framework\Model\Abstract_Model
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorization\Model\Resource_Model\Rules::class);
    }
    /**
     * Obsolete method of update
     *
     * @return $this
     * @deprecated Method was never implemented and used.
     */
    public function update(): static
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        trigger_error('Method was never implemented and used.', E_USER_DEPRECATED);
        return $this;
    }
    /**
     * Save authorization rule relation
     *
     * @return $this
     */
    public function save_rel(): static
    {
        $this->get_resource()->save_rel($this);
        return $this;
    }
}