<?php

declare(strict_types=1);
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Attribute $model */
$attribute = Bootstrap::getObjectManager()->create(Attribute::class);
/** @var AttributeRepository $attributeRepository */
$attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
$attribute->loadByCode('customer_address', 'telephone');
$attribute->setIsRequired(false);
$attributeRepository->save($attribute);
