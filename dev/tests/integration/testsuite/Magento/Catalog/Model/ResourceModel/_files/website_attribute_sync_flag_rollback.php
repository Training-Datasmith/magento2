<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\FlagManager;

/**
 * @var FlagManager $flagManager
 */
$flagManager = ObjectManager::getInstance()->get(FlagManager::class);
$flagManager->deleteFlag(WebsiteAttributesSynchronizer::FLAG_NAME);
