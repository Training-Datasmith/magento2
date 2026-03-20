<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Admin_Notification;

/**
 * Class Plugin to eliminate Bulk related links in the notification area
 */
class Plugin
{
    /**
     * @var bool
     */
    private $is_allowed;
    /**
     * Plugin constructor.
     */
    public function __construct(private readonly \Magento\Framework\Authorization_Interface $authorization)
    {
    }
    /**
     * Prepares Meta
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_get_meta(\Magento\Admin_Notification\Ui\Component\Data_Provider\Data_Provider $data_provider, array $result): array
    {
        if (!isset($this->is_allowed)) {
            $this->is_allowed = $this->authorization->is_allowed('Magento_Logging::system_magento_logging_bulk_operations');
        }
        $result['columns']['arguments']['data']['config']['isAllowed'] = $this->is_allowed;
        return $result;
    }
}