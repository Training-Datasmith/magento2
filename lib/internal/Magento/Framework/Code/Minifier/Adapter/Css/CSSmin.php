<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Minifier\Adapter\Css;

use Magento\Framework\Code\Minifier\Adapter_Interface;
use tubalmartin\Css_Min\Minifier as CssMinLibrary;
/**
 * Adapter for CSSmin library
 */
class Cs_Smin implements Adapter_Interface
{
    /**
     * 'pcre.recursion_limit' value for CSSMin minification
     */
    public const PCRE_RECURSION_LIMIT = 1000;
    /**
     * @var CssMinLibrary
     */
    protected $css_minifier;
    /**
     * @param CssMinLibrary $cssMinifier
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Css_Min_Library $css_minifier)
    {
        // TODO: set $cssMinifier in constructor once MAGETWO-51176 is resolved.
    }
    /**
     * Get CSS Minifier
     *
     * @return CssMinLibrary
     */
    private function get_css_min()
    {
        if (!$this->css_minifier instanceof Css_Min_Library) {
            $this->css_minifier = new Css_Min_Library(false);
        }
        return $this->css_minifier;
    }
    /**
     * Minify css file content
     *
     * @param string $content
     * @return string
     */
    public function minify($content)
    {
        $pcre_recursion_limit = ini_get('pcre.recursion_limit');
        ini_set('pcre.recursion_limit', self::PCRE_RECURSION_LIMIT);
        $result = $this->get_css_min()->run($content);
        ini_set('pcre.recursion_limit', $pcre_recursion_limit);
        return $result;
    }
}