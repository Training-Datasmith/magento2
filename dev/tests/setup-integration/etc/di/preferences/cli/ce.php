<?php

declare(strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

use Magento\Framework as MF;
use Magento\TestFramework as TF;

return [
    MF\App\AreaList::class => TF\App\AreaList::class,
    MF\Mview\TriggerCleaner::class => TF\Mview\DummyTriggerCleaner::class,
];
