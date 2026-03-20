<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
if (PHP_SAPI === 'cli') {
    \Magento\Framework\Console\Command_Locator::register(\Magento\Backend\Console\Command_List::class);
}