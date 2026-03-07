<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\Directory\Model\Region as RegionModel;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionResourceCollection;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$regionCode = ['ER1', 'ER2'];

/** @var RegionResource $regionResource */
$regionResource = $objectManager->get(RegionResource::class);

$regionCollection = $objectManager->create(RegionResourceCollection::class)
    ->addFieldToFilter('code', ['in' => $regionCode]);

/** @var RegionModel $region */
foreach ($regionCollection as $region) {
    $regionResource->delete($region);
}
