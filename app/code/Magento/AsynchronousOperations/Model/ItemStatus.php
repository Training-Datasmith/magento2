<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Item_Status_Interface;
use Magento\Framework\Data_Object;
class Item_Status extends Data_Object implements Item_Status_Interface
{
    /**
     * @inheritDoc
     */
    public function get_id()
    {
        return $this->get_data(self::ENTITY_ID);
    }
    /**
     * @inheritDoc
     */
    public function set_id($entity_id)
    {
        return $this->set_data(self::ENTITY_ID, $entity_id);
    }
    /**
     * @inheritDoc
     */
    public function get_data_hash()
    {
        return $this->get_data(self::DATA_HASH);
    }
    /**
     * @inheritDoc
     */
    public function set_data_hash($hash)
    {
        return $this->set_data(self::DATA_HASH, $hash);
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
    public function set_status($status = self::STATUS_ACCEPTED)
    {
        return $this->set_data(self::STATUS, $status);
    }
    /**
     * @inheritDoc
     */
    public function get_error_message()
    {
        return $this->get_data(self::ERROR_MESSAGE);
    }
    /**
     * @inheritDoc
     */
    public function set_error_message($error_message = null)
    {
        if ($error_message instanceof \Exception) {
            $error_message = $error_message->get_message();
        }
        return $this->set_data(self::ERROR_MESSAGE, $error_message);
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
    public function set_error_code($error_code = null)
    {
        if ($error_code instanceof \Exception) {
            $error_code = $error_code->get_code();
        }
        return $this->set_data(self::ERROR_CODE, (int) $error_code);
    }
}