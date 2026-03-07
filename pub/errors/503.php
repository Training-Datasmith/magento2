<?php

declare(strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

require_once 'processorFactory.php';

$processorFactory = new \Magento\Framework\Error\ProcessorFactory();
$processor = $processorFactory->createProcessor();
$response = $processor->process503();
$response->sendResponse();
