<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
$attribute->loadByCode(4, 'foo');

if ($attribute->getId()) {
    $attribute->delete();
}

/** @var Set $attributeSet */
$attributeSet = $objectManager->create(Set::class)->load('test_attribute_set', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
