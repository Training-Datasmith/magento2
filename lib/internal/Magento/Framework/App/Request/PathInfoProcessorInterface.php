<?php

declare (strict_types=1);
/**
 * PATH_INFO processor
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Request;

/**
 * @api
 * @since 100.0.2
 */
interface Path_Info_Processor_Interface
{
    /**
     * Process Request path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\Request_Interface $request, $path_info);
}