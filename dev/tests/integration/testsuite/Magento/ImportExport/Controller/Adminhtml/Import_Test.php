<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\ImportExport\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class ImportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testGetFilterAction()
    {
        $this->dispatch('backend/admin/import/index');
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString(
            (string)\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\ImportExport\Helper\Data::class
            )->getMaxUploadSizeMessage(),
            $body
        );
    }
}
