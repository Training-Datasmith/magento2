<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
$attribute->load('boolean_attribute', 'attribute_code');
$attribute->delete();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
