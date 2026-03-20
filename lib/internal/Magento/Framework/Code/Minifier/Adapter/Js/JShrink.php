<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Minifier\Adapter\Js;

use J_Shrink\Minifier;
use Magento\Framework\Code\Minifier\Adapter_Interface;
/**
 * Adapter for JShrink library
 */
class J_Shrink implements Adapter_Interface
{
    /**
     * Takes a string containing javascript and removes unneeded characters in
     * order to shrink the code without altering it's functionality.
     *
     * @param string $content The raw javascript to be minified
     * @throws \Exception
     * @return bool|string
     */
    public function minify($content)
    {
        return Minifier::minify($content);
    }
}