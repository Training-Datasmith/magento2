<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Dependency\Report;

/**
 *  Builder Interface
 */
interface BuilderInterface
{
    /**
     * Build a report
     *
     * @param array $options
     * @return void
     */
    public function build(array $options);
}
