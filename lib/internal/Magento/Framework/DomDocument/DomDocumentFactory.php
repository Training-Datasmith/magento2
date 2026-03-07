<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 */
class DomDocumentFactory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function create()
    {
        return new \DOMDocument();
    }
}
