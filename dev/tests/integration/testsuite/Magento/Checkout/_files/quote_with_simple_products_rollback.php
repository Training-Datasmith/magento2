<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $order Quote */
$quoteCollection = Bootstrap::getObjectManager()->create(Collection::class);
foreach ($quoteCollection as $quote) {
    $quote->delete();
}

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products_rollback.php');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
