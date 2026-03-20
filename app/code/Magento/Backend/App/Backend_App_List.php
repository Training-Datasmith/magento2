<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

/**
 * List of Backend Applications to allow injection of them through the DI
 * @api
 * @since 100.0.2
 */
class Backend_App_List
{
    public function __construct(private readonly \Magento\Framework\App\Request\Http $request, private array $backend_apps = [])
    {
    }
    /**
     * Get Backend app based on its name
     *
     * @return BackendApp|null
     */
    public function get_current_app()
    {
        $app_name = $this->request->get_query('app');
        if ($app_name && isset($this->backend_apps[$app_name])) {
            return $this->backend_apps[$app_name];
        }
        return null;
    }
    /**
     * Retrieve backend application by name
     *
     * @param string $appName
     * @return BackendApp|null
     */
    public function get_backend_app($app_name)
    {
        return $this->backend_apps[$app_name] ?? null;
    }
}