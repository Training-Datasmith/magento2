<?php

declare(strict_types=1);
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
class BackendAppList
{
    public function __construct(private readonly \Magento\Framework\App\Request\Http $request, private array $backendApps = [])
    {
    }

    /**
     * Get Backend app based on its name
     *
     * @return BackendApp|null
     */
    public function getCurrentApp()
    {
        $appName = $this->request->getQuery('app');
        if ($appName && isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }

    /**
     * Retrieve backend application by name
     *
     * @param string $appName
     * @return BackendApp|null
     */
    public function getBackendApp($appName)
    {
        return $this->backendApps[$appName] ?? null;
    }
}
