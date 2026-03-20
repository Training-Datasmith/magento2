<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Builder for Filter Service Data Object.
 *
 * @api
 * @method Filter create()
 * @since 100.0.2
 */
class Filter_Builder extends Abstract_Simple_Object_Builder
{
    /**
     * Set field
     *
     * @param string $field
     * @return $this
     */
    public function set_field($field)
    {
        $this->data['field'] = $field;
        return $this;
    }
    /**
     * Set value
     *
     * @param string|array $value
     * @return $this
     */
    public function set_value($value)
    {
        $this->data['value'] = $value;
        return $this;
    }
    /**
     * Set condition type
     *
     * @param string $conditionType
     * @return $this
     */
    public function set_condition_type($condition_type)
    {
        $this->data['condition_type'] = $condition_type;
        return $this;
    }
}