<?php

declare (strict_types=1);
/**
 * No such entity service exception
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
/**
 * @api
 * @since 100.0.2
 */
class No_Such_Entity_Exception extends Localized_Exception
{
    /**
     * @deprecated
     */
    public const MESSAGE_SINGLE_FIELD = 'No such entity with %fieldName = %fieldValue';
    /**
     * @deprecated
     */
    public const MESSAGE_DOUBLE_FIELDS = 'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value';
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('No such entity.');
        }
        parent::__construct($phrase, $cause, $code);
    }
    /**
     * Helper function for creating an exception when a single field is responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     */
    public static function single_field($field_name, $field_value)
    {
        return new self(new Phrase('No such entity with %fieldName = %fieldValue', ['fieldName' => $field_name, 'fieldValue' => $field_value]));
    }
    /**
     * Helper function for creating an exception when two fields are responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @param string $secondFieldName
     * @param string|int $secondFieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     */
    public static function double_field($field_name, $field_value, $second_field_name, $second_field_value)
    {
        return new self(new Phrase('No such entity with %fieldName = %fieldValue, %field2Name = %field2Value', ['fieldName' => $field_name, 'fieldValue' => $field_value, 'field2Name' => $second_field_name, 'field2Value' => $second_field_value]));
    }
}