<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * Wysiwyg editor configuration path
     */
    public const WYSIWYG_EDITOR_CONFIG_PATH = 'cms/wysiwyg/editor';

    /**
     * Configuration path to session storage logging setting
     */
    public const XML_PATH_LOGGING = 'dev/js/session_storage_logging';

    /**
     * Configuration path to session storage key setting
     */
    public const XML_PATH_KEY = 'dev/js/session_storage_key';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is session storage logging enabled
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LOGGING);
    }

    /**
     * Get session storage key
     *
     * @return string
     */
    public function getSessionStorageKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_KEY);
    }
}
