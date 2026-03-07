<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;

/**
 * Massaction key processor
 */
class MassactionKey
{
    /**
     * Process massaction key
     *
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request): void
    {
        $key = $request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData ?? '');
            $request->setPostValue($key, $value ?: null);
        }
    }
}
