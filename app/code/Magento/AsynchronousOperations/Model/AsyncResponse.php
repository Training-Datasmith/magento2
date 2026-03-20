<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Async_Response_Interface;
use Magento\Framework\Api\Extensible_Data_Interface;
use Magento\Framework\Data_Object;
class Async_Response extends Data_Object implements Async_Response_Interface, Extensible_Data_Interface
{
    /**
     * @inheritDoc
     */
    public function get_bulk_uuid()
    {
        return $this->get_data(self::BULK_UUID);
    }
    /**
     * @inheritDoc
     */
    public function set_bulk_uuid($bulk_uuid)
    {
        return $this->set_data(self::BULK_UUID, $bulk_uuid);
    }
    /**
     * @inheritDoc
     */
    public function get_request_items()
    {
        return $this->get_data(self::REQUEST_ITEMS);
    }
    /**
     * @inheritDoc
     */
    public function set_request_items($request_items)
    {
        return $this->set_data(self::REQUEST_ITEMS, $request_items);
    }
    /**
     * @inheritdoc
     */
    public function set_errors($is_errors = false)
    {
        return $this->set_data(self::ERRORS, $is_errors);
    }
    /**
     * @inheritdoc
     */
    public function is_errors()
    {
        return $this->get_data(self::ERRORS);
    }
    /**
     * @inheritDoc
     */
    public function get_extension_attributes()
    {
        return $this->get_data(self::EXTENSION_ATTRIBUTES_KEY);
    }
    /**
     * @inheritDoc
     */
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Async_Response_Extension_Interface $extension_attributes)
    {
        return $this->set_data(self::EXTENSION_ATTRIBUTES_KEY, $extension_attributes);
    }
}