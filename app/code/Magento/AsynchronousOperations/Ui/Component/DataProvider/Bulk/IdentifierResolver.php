<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk;

use Magento\Framework\App\RequestInterface;

/**
 * Class IdentifierResolver
 */
class IdentifierResolver
{
    public function __construct(private readonly RequestInterface $request)
    {
    }

    /**
     * @return null|string
     */
    public function execute()
    {
        return $this->request->getParam('uuid');
    }
}
