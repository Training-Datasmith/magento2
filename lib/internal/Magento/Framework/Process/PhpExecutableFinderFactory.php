<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Process;

class PhpExecutableFinderFactory
{
    /**
     * Create PhpExecutableFinder instance
     *
     * @return \Symfony\Component\Process\PhpExecutableFinder
     */
    public function create()
    {
        return new \Symfony\Component\Process\PhpExecutableFinder();
    }
}
