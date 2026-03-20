<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Response_Handler;

use Magento\Analytics\Model\Connector\Http\Response_Handler_Interface;
/**
 * Return positive answer that request was finished successfully.
 */
class Update implements Response_Handler_Interface
{
    /**
     * @inheritdoc
     */
    public function handle_response(array $response_body): bool
    {
        return true;
    }
}