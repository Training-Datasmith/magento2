<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Phrase;
/**
 * Data object for sort order.
 *
 * @api
 * @since 100.0.2
 */
class Sort_Order extends Abstract_Simple_Object
{
    public const FIELD = 'field';
    public const DIRECTION = 'direction';
    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';
    /**
     * Initialize object and validate sort direction
     *
     * @param array $data
     * @throws InputException
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        if (null !== $this->get_direction()) {
            $this->validate_direction($this->get_direction());
        }
        if ($this->get_field() !== null) {
            $this->validate_field($this->get_field());
        }
    }
    /**
     * Get sorting field.
     *
     * @return string
     */
    public function get_field()
    {
        return $this->_get(Sort_Order::FIELD);
    }
    /**
     * Set sorting field.
     *
     * @param string $field
     * @throws InputException
     *
     * @return $this
     */
    public function set_field($field)
    {
        $this->validate_field($field);
        return $this->set_data(Sort_Order::FIELD, $field);
    }
    /**
     * Get sorting direction.
     *
     * @return string
     */
    public function get_direction()
    {
        return $this->_get(Sort_Order::DIRECTION);
    }
    /**
     * Set sorting direction.
     *
     * @param string $direction
     * @throws InputException
     *
     * @return $this
     */
    public function set_direction($direction)
    {
        $this->validate_direction($direction);
        return $this->set_data(Sort_Order::DIRECTION, $this->normalize_direction_input($direction));
    }
    /**
     * Validate direction argument ASC or DESC
     *
     * @param mixed $direction
     * @return void
     * @throws InputException
     */
    private function validate_direction($direction): void
    {
        $this->validate_direction_is_string($direction);
        $this->validate_direction_is_asc_or_desc($direction);
    }
    /**
     * @param string $direction
     * @throws InputException
     * @return void
     */
    private function validate_direction_is_string($direction): void
    {
        if (!is_string($direction)) {
            throw new Input_Exception(new Phrase('The sort order has to be specified as a string, got %1.', [gettype($direction)]));
        }
    }
    /**
     * @param string $direction
     * @throws InputException
     * @return void
     */
    private function validate_direction_is_asc_or_desc($direction): void
    {
        $normalized_direction = $this->normalize_direction_input($direction);
        if (!in_array($normalized_direction, [Sort_Order::SORT_ASC, Sort_Order::SORT_DESC], true)) {
            throw new Input_Exception(new Phrase('The sort order has to be specified as %1 for ascending order or %2 for descending order.', [Sort_Order::SORT_ASC, Sort_Order::SORT_DESC]));
        }
    }
    /**
     * @param string $direction
     * @return string
     */
    private function normalize_direction_input($direction)
    {
        return strtoupper($direction);
    }
    /**
     * Check if given value can be used as sorting field.
     *
     * @param string $field
     * @return void
     * @throws InputException
     */
    private function validate_field(string $field): void
    {
        if (preg_match('/[^a-z0-9\_]/i', $field)) {
            throw new Input_Exception(new Phrase('Sort order field %1 contains restricted symbols', [$field]));
        }
    }
}