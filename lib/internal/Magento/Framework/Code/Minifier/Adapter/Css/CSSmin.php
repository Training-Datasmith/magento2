<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Code\Minifier\Adapter\Css;

use Magento\Framework\Code\Minifier\AdapterInterface;
use tubalmartin\CssMin\Minifier as CssMinLibrary;

/**
 * Adapter for CSSmin library
 */
class CSSmin implements AdapterInterface
{
    /**
     * 'pcre.recursion_limit' value for CSSMin minification
     */
    public const PCRE_RECURSION_LIMIT = 1000;

    /**
     * @var CssMinLibrary
     */
    protected $cssMinifier;

    /**
     * @param CssMinLibrary $cssMinifier
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CssMinLibrary $cssMinifier)
    {
        // TODO: set $cssMinifier in constructor once MAGETWO-51176 is resolved.
    }

    /**
     * Get CSS Minifier
     *
     * @return CssMinLibrary
     */
    private function getCssMin()
    {
        if (!($this->cssMinifier instanceof CssMinLibrary)) {
            $this->cssMinifier = new CssMinLibrary(false);
        }
        return $this->cssMinifier;
    }

    /**
     * Minify css file content
     *
     * @param string $content
     * @return string
     */
    public function minify($content)
    {
        $pcreRecursionLimit = ini_get('pcre.recursion_limit');
        ini_set('pcre.recursion_limit', self::PCRE_RECURSION_LIMIT);
        $result = $this->getCssMin()->run($content);
        ini_set('pcre.recursion_limit', $pcreRecursionLimit);
        return $result;
    }
}
