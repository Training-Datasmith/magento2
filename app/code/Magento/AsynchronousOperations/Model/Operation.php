<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Framework\Data_Object;
/**
 * Class Operation encapsulates methods for Operation Model Object
 */
class Operation extends Data_Object implements Operation_Interface
{
    /**
     * Operation constructor.
     */
    public function __construct(private readonly Operation_Status_Validator $operation_status_validator, array $data = [])
    {
        parent::__construct($data);
    }
    /**
     * @inheritDoc
     */
    public function get_id()
    {
        return $this->get_data(self::ID);
    }
    /**
     * @inheritDoc
     */
    public function set_id($id)
    {
        return $this->set_data(self::ID, $id);
    }
    /**
     * @inheritDoc
     */
    public function get_bulk_uuid()
    {
        return $this->get_data(self::BULK_ID);
    }
    /**
     * @inheritDoc
     */
    public function set_bulk_uuid($bulk_id)
    {
        return $this->set_data(self::BULK_ID, $bulk_id);
    }
    /**
     * @inheritDoc
     */
    public function get_topic_name()
    {
        return $this->get_data(self::TOPIC_NAME);
    }
    /**
     * @inheritDoc
     */
    public function set_topic_name($topic)
    {
        return $this->set_data(self::TOPIC_NAME, $topic);
    }
    /**
     * @inheritDoc
     */
    public function get_serialized_data()
    {
        return $this->get_data(self::SERIALIZED_DATA);
    }
    /**
     * @inheritDoc
     */
    public function set_serialized_data($serialized_data)
    {
        return $this->set_data(self::SERIALIZED_DATA, $serialized_data);
    }
    /**
     * @inheritDoc
     */
    public function get_result_serialized_data()
    {
        return $this->get_data(self::RESULT_SERIALIZED_DATA);
    }
    /**
     * @inheritDoc
     */
    public function set_result_serialized_data($result_serialized_data)
    {
        return $this->set_data(self::RESULT_SERIALIZED_DATA, $result_serialized_data);
    }
    /**
     * @inheritDoc
     */
    public function get_status()
    {
        return $this->get_data(self::STATUS);
    }
    /**
     * @inheritDoc
     */
    public function set_status($status)
    {
        $this->operation_status_validator->validate($status);
        return $this->set_data(self::STATUS, $status);
    }
    /**
     * @inheritDoc
     */
    public function get_result_message()
    {
        return $this->get_data(self::RESULT_MESSAGE);
    }
    /**
     * @inheritDoc
     */
    public function set_result_message($result_message)
    {
        return $this->set_data(self::RESULT_MESSAGE, $result_message);
    }
    /**
     * @inheritDoc
     */
    public function get_error_code()
    {
        return $this->get_data(self::ERROR_CODE);
    }
    /**
     * @inheritDoc
     */
    public function set_error_code($error_code)
    {
        return $this->set_data(self::ERROR_CODE, $error_code);
    }
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface|null
     */
    public function get_extension_attributes()
    {
        return $this->get_data(self::EXTENSION_ATTRIBUTES_KEY);
    }
    /**
     * Set an extension attributes object.
     *
     * @return $this
     */
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Operation_Extension_Interface $extension_attributes)
    {
        return $this->set_data(self::EXTENSION_ATTRIBUTES_KEY, $extension_attributes);
    }
}