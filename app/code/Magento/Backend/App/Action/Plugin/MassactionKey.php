<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\Backend\App\Abstract_Action;
use Magento\Framework\App\Request_Interface;
/**
 * Massaction key processor
 */
class Massaction_Key
{
    /**
     * Process massaction key
     *
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before_dispatch(Abstract_Action $subject, Request_Interface $request): void
    {
        $key = $request->get_post('massaction_prepare_key');
        if ($key) {
            $post_data = $request->get_post($key);
            $value = is_array($post_data) ? $post_data : explode(',', $post_data ?? '');
            $request->set_post_value($key, $value ?: null);
        }
    }
}