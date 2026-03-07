<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminAnalytics\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data Provider for the Admin usage UI component.
 */
class AdminUsageNotificationDataProvider extends AbstractDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter): null
    {
        return null;
    }
}
