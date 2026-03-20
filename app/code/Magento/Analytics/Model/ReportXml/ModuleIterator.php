<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Report_Xml;

use Magento\Framework\Module\Manager as ModuleManager;
/**
 * Iterator for ReportXml modules
 */
class Module_Iterator extends \Iterator_Iterator
{
    public function __construct(private readonly Module_Manager $module_manager, \Traversable $iterator)
    {
        parent::__construct($iterator);
    }
    /**
     * Returns module with module status
     *
     * @return array
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        $current = parent::current();
        if (is_array($current) && isset($current['module_name'])) {
            $current['status'] = $this->module_manager->is_enabled($current['module_name']) == 1 ? 'Enabled' : 'Disabled';
        }
        return $current;
    }
}