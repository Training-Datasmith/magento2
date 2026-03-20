<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\View_Model;

/**
 * View model interface for requirejs configuration modifier
 */
interface Require_Js_Config_Modifier_Interface
{
    /**
     * Modifies requirejs configuration
     *
     * @param array $config requirejs configuration
     * @return array
     */
    public function modify(array $config): array;
}