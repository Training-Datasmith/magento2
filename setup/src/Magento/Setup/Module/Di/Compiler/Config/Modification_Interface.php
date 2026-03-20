<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

/**
 * Interface \Magento\Setup\Module\Di\Compiler\Config\ModificationInterface
 *
 */
interface ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config);
}
