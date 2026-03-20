<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Request;

use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\App\Request\Path_Info_Processor_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Store\App\Request\Path_Info_Processor as AppPathInfoProcessor;
/**
 * Prevents path info processing for admin store
 *
 * @api
 * @since 100.0.2
 */
class Path_Info_Processor implements Path_Info_Processor_Interface
{
    /**
     * @var HelperData
     */
    private $_helper;
    /**
     * @var AppPathInfoProcessor
     */
    private $_subject;
    /**
     * @param AppPathInfoProcessor $subject
     * @param HelperData $helper
     */
    public function __construct(App_Path_Info_Processor $subject, Helper_Data $helper)
    {
        $this->_helper = $helper;
        $this->_subject = $subject;
    }
    /**
     * Process path info
     *
     * @param RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(Request_Interface $request, $path_info)
    {
        $first_part = $path_info === null ? '' : explode('/', ltrim($path_info, '/'), 2)[0];
        if ($first_part != $this->_helper->get_area_front_name()) {
            return $this->_subject->process($request, $path_info);
        }
        return $path_info;
    }
}