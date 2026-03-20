<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model\Bulk_User_Type;

use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\Data\Option_Source_Interface;
class Options implements Option_Source_Interface
{
    /**
     * @inheritDoc
     */
    public function to_option_array(): array
    {
        return [['value' => User_Context_Interface::USER_TYPE_ADMIN, 'label' => __('Admin user')], ['value' => User_Context_Interface::USER_TYPE_INTEGRATION, 'label' => __('Integration')]];
    }
}