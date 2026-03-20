<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Model\Entity;

use Magento\Async_Config\Api\Data\Async_Config_Message_Interface;
class Async_Config_Message implements Async_Config_Message_Interface
{
    /**
     * @var string
     */
    private $data;
    /**
     * @inheritDoc
     */
    public function get_config_data()
    {
        return $this->data;
    }
    /**
     * @inheritDoc
     */
    public function set_config_data($data): void
    {
        $this->data = $data;
    }
}