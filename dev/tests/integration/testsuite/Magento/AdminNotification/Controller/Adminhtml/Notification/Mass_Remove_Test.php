<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\Framework\App\Request\Http as HttpRequest;

class MassRemoveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::adminnotification_remove';
        $this->uri = 'backend/admin/notification/massremove';
        $this->httpMethod = HttpRequest::METHOD_POST;
        parent::setUp();
    }
}
