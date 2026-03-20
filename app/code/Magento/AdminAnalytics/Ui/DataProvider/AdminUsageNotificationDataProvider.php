<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Analytics\Ui\Data_Provider;

use Magento\Framework\Api\Filter;
use Magento\Ui\Data_Provider\Abstract_Data_Provider;
/**
 * Data Provider for the Admin usage UI component.
 */
class Admin_Usage_Notification_Data_Provider extends Abstract_Data_Provider
{
    /**
     * @inheritdoc
     */
    public function get_data()
    {
        return $this->data;
    }
    /**
     * @inheritdoc
     */
    public function add_filter(Filter $filter): null
    {
        return null;
    }
}