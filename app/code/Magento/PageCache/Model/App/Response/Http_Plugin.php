<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\PageCache\Model\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * @param Context $context
     * @param HttpRequest $request
     */
    public function __construct(
        private Context $context,
        private HttpRequest $request
    ) {
    }

    /**
     * Set proper value of X-Magento-Vary cookie.
     *
     * @param HttpResponse $subject
     * @return void
     */
    public function beforeSendResponse(HttpResponse $subject)
    {
        if ($subject instanceof NotCacheableInterface
            || $subject->headersSent()
            || $subject->getMetadata('NotCacheable')
        ) {
            return;
        }

        $currentVary = $this->context->getVaryString();
        $varyCookie = $this->request->get(HttpResponse::COOKIE_VARY_STRING);
        if (isset($varyCookie) && ($currentVary !== $varyCookie)) {
            $subject->setNoCacheHeaders();
        }
        $subject->sendVary();
    }
}
