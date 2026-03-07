<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\AdminNotification;

/**
 * Class Plugin to eliminate Bulk related links in the notification area
 */
class Plugin
{
    /**
     * @var bool
     */
    private $isAllowed;

    /**
     * Plugin constructor.
     */
    public function __construct(private readonly \Magento\Framework\AuthorizationInterface $authorization)
    {
    }

    /**
     * Prepares Meta
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(
        \Magento\AdminNotification\Ui\Component\DataProvider\DataProvider $dataProvider,
        array $result
    ): array {
        if (!isset($this->isAllowed)) {
            $this->isAllowed = $this->authorization->isAllowed(
                'Magento_Logging::system_magento_logging_bulk_operations'
            );
        }
        $result['columns']['arguments']['data']['config']['isAllowed'] = $this->isAllowed;
        return $result;
    }
}
