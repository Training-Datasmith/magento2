<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Workaround for occasional non-zero exit code (exec returned: 139) caused by the PHP bug
 */

namespace Magento\TestFramework\Workaround;

class Segfault
{
    /**
     * Force garbage collection
     */
    public function endTestSuite()
    {
        gc_collect_cycles();
    }
}
