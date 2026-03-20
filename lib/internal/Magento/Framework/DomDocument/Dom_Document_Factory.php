<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Dom_Document;

/**
 * DOM document factory
 */
class Dom_Document_Factory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function create()
    {
        return new \Dom_Document();
    }
}