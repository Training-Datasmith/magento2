<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Setup\Patch\Data;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Disable default frontend SID
 */
class DisableSid implements DataPatchInterface, PatchVersionInterface
{
    /**
     * Config path for flag whether use SID on frontend
     */
    public const XML_PATH_USE_FRONTEND_SID = 'web/session/use_frontend_sid';

    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * scope type
     */
    public const SCOPE_STORE = 'store';

    /**
     * Disable Sid constructor.
     *
     * @param MutableScopeConfigInterface $mutableScopeConfig
     */
    public function __construct(
        MutableScopeConfigInterface $mutableScopeConfig
    ) {
        $this->mutableScopeConfig = $mutableScopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->mutableScopeConfig->setValue(self::XML_PATH_USE_FRONTEND_SID, 0, self::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
