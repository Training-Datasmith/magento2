<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Model;

/**
 * Backend user session
 *
 * @api
 * @since 100.0.2
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
