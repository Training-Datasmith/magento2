<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Service\V1;

/**
 * Module service.
 */
class Module_Service implements Module_Service_Interface
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $module_list;
    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(\Magento\Framework\Module\Module_List_Interface $module_list)
    {
        $this->module_list = $module_list;
    }
    /**
     * Returns an array of enabled modules
     *
     * @return string[]
     */
    public function get_modules()
    {
        return $this->module_list->get_names();
    }
}