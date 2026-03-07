<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$logFactory = $objectManager->get(LogFactory::class);

/** @var Log $captchaLog */
$captchaLog = $logFactory->create();
$captchaLog->logAttempt('mageadmin');
