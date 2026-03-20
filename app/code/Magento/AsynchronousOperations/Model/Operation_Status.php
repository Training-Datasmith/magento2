<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Api\Data\Summary_Operation_Status_Interface;
use Magento\Framework\Api\Extensible_Data_Interface;
use Magento\Framework\Data_Object;
/**
 * Class OperationShortDetails
 */
class Operation_Status extends Data_Object implements Summary_Operation_Status_Interface, Extensible_Data_Interface
{
    /**
     * @inheritDoc
     */
    public function get_id()
    {
        return $this->get_data(Operation_Interface::ID);
    }
    /**
     * @inheritDoc
     */
    public function get_status()
    {
        return $this->get_data(Operation_Interface::STATUS);
    }
    /**
     * @inheritDoc
     */
    public function get_result_message()
    {
        return $this->get_data(Operation_Interface::RESULT_MESSAGE);
    }
    /**
     * @inheritDoc
     */
    public function get_error_code()
    {
        return $this->get_data(Operation_Interface::ERROR_CODE);
    }
}