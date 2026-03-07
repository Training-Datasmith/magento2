<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

class XContentTypeOptions extends AbstractHeaderProvider
{
    /**
     * @var string
     */
    protected $headerValue = 'nosniff';

    /**
     * @var string
     */
    protected $headerName = 'X-Content-Type-Options';
}
