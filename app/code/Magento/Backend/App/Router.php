<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

/**
 * @api
 * @since 100.0.2
 */
class Router extends \Magento\Framework\App\Router\Base
{
    /**
     * @var \Magento\Framework\UrlInterface $url
     */
    protected $_url;
    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     */
    protected $_required_params = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];
    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @var bool
     */
    protected $apply_no_route = true;
    /**
     * @var string
     */
    protected $path_prefix = \Magento\Backend\App\Area\Front_Name_Resolver::AREA_CODE;
    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _should_redirect_to_secure()
    {
        return false;
    }
}