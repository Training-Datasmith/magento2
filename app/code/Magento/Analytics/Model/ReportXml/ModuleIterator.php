<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\ReportXml;

use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Iterator for ReportXml modules
 */
class ModuleIterator extends \IteratorIterator
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        \Traversable $iterator
    ) {
        parent::__construct($iterator);
    }

    /**
     * Returns module with module status
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $current = parent::current();
        if (is_array($current) && isset($current['module_name'])) {
            $current['status'] =
                $this->moduleManager->isEnabled($current['module_name']) == 1 ? 'Enabled' : 'Disabled';
        }
        return $current;
    }
}
