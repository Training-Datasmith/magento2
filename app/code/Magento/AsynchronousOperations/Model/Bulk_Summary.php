<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Framework\Data_Object;
/**
 * Class BulkSummary
 */
class Bulk_Summary extends Data_Object implements Bulk_Summary_Interface, \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * @inheritDoc
     */
    public function get_bulk_id()
    {
        return $this->get_data(self::BULK_ID);
    }
    /**
     * @inheritDoc
     */
    public function set_bulk_id($bulk_uuid)
    {
        return $this->set_data(self::BULK_ID, $bulk_uuid);
    }
    /**
     * @inheritDoc
     */
    public function get_description()
    {
        return $this->get_data(self::DESCRIPTION);
    }
    /**
     * @inheritDoc
     */
    public function set_description($description)
    {
        return $this->set_data(self::DESCRIPTION, $description);
    }
    /**
     * @inheritDoc
     */
    public function get_start_time()
    {
        return $this->get_data(self::START_TIME);
    }
    /**
     * @inheritDoc
     */
    public function set_start_time($timestamp)
    {
        return $this->set_data(self::START_TIME, $timestamp);
    }
    /**
     * @inheritDoc
     */
    public function get_user_id()
    {
        return $this->get_data(self::USER_ID);
    }
    /**
     * @inheritDoc
     */
    public function set_user_id($user_id)
    {
        return $this->set_data(self::USER_ID, $user_id);
    }
    /**
     * @inheritDoc
     */
    public function get_user_type()
    {
        return $this->get_data(self::USER_TYPE);
    }
    /**
     * @inheritDoc
     */
    public function set_user_type($user_type)
    {
        return $this->set_data(self::USER_TYPE, $user_type);
    }
    /**
     * @inheritDoc
     */
    public function get_operation_count()
    {
        return $this->get_data(self::OPERATION_COUNT);
    }
    /**
     * @inheritDoc
     */
    public function set_operation_count($operation_count)
    {
        return $this->set_data(self::OPERATION_COUNT, $operation_count);
    }
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface|null
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
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Extension_Interface $extension_attributes)
    {
        return $this->set_data(self::EXTENSION_ATTRIBUTES_KEY, $extension_attributes);
    }
}