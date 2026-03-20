<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Builder for sort order data object.
 * @method SortOrder create()
 *
 * @api
 * @since 100.0.2
 */
class Sort_Order_Builder extends Abstract_Simple_Object_Builder
{
    /**
     * Set sorting field.
     *
     * @param string $field
     * @return $this
     */
    public function set_field($field)
    {
        $this->_set(Sort_Order::FIELD, $field);
        return $this;
    }
    /**
     * Set sorting direction.
     *
     * @param string $direction
     * @return $this
     */
    public function set_direction($direction)
    {
        $this->_set(Sort_Order::DIRECTION, $direction);
        return $this;
    }
    /**
     * @return $this
     */
    public function set_ascending_direction()
    {
        $this->set_direction(Sort_Order::SORT_ASC);
        return $this;
    }
    /**
     * @return $this
     */
    public function set_descending_direction()
    {
        $this->set_direction(Sort_Order::SORT_DESC);
        return $this;
    }
}