<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Search\Api\Data\SynonymGroupInterface;
use Magento\Search\Model\SynonymGroupRepository;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$synonymsGroupModel = $objectManager->create(SynonymGroupInterface::class);
$synonymGroupRepository = $objectManager->create(SynonymGroupRepository::class);
$synonymsGroupModel->setStoreId(Magento\Store\Model\Store::DEFAULT_STORE_ID)->setStoreId(0)->setWebsiteId(0);

$synonymGroupRepository->save($synonymsGroupModel);
