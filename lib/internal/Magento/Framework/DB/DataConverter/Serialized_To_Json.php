<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All rights reserved.
 */
namespace Magento\Framework\DB\Data_Converter;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
/**
 * Convert from serialized to JSON format
 */
class Serialized_To_Json implements Data_Converter_Interface
{
    /**
     * @var Serialize
     */
    private $serialize;
    /**
     * @var Json
     */
    private $json;
    /**
     * Constructor
     *
     * @param Serialize $serialize
     * @param Json $json
     */
    public function __construct(Serialize $serialize, Json $json)
    {
        $this->serialize = $serialize;
        $this->json = $json;
    }
    /**
     * Convert from serialized to JSON format
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        if ($this->is_valid_json_value($value)) {
            return $value;
        }
        return $this->encode_json($this->unserialize_value($value));
    }
    /**
     * Is a valid JSON serialized value
     *
     * @param string $value
     * @return bool
     */
    protected function is_valid_json_value($value)
    {
        if (in_array($value, ['null', 'false', '0', '""', '[]']) || json_decode($value) !== null && json_last_error() === JSON_ERROR_NONE) {
            return true;
        }
        //JSON last error reset
        json_encode([]);
        return false;
    }
    /**
     * Unserialize value
     *
     * @param string $value
     * @return mixed
     * @throws DataConversionException
     */
    protected function unserialize_value($value)
    {
        try {
            set_error_handler(function ($error_number, $error_string) {
                throw new Data_Conversion_Exception($error_string, $error_number);
            });
            $value = $this->serialize->unserialize($value);
        } catch (\Throwable $throwable) {
            throw new Data_Conversion_Exception($throwable->get_message());
        } finally {
            restore_error_handler();
        }
        return $value;
    }
    /**
     * Encode value with json encoder.
     *
     * For data consistency during converting process PG(serialize_precision) is set to 17.
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    protected function encode_json($value)
    {
        $stored_serialize_precision = ini_get('serialize_precision');
        // In PHP 8.1+ json_encode() uses PG(serialize_precision)
        ini_set('serialize_precision', 17);
        $value = $this->json->serialize($value);
        ini_set('serialize_precision', $stored_serialize_precision);
        if (json_last_error()) {
            throw new Data_Conversion_Exception(json_last_error_msg());
        }
        return $value;
    }
}