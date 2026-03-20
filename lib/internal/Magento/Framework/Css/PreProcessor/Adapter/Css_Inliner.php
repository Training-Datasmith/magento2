<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\Adapter;

use Magento\Framework\App\State;
use Pelago\Emogrifier\Css_Inliner as EmogrifierCssInliner;
use Symfony\Component\Css_Selector\Exception\Parse_Exception;
/**
 * This class will inline the css of an html to each tag to be used for applications such as a styled email.
 */
class Css_Inliner
{
    /**
     * @var State
     */
    private $app_state;
    /**
     * @var string
     */
    private $html = '';
    /**
     * @var string
     */
    private $css = '';
    /**
     * @var bool
     */
    private $disable_style_blocks_parsing = false;
    /**
     * @param State $appState
     */
    public function __construct(State $app_state)
    {
        $this->app_state = $app_state;
    }
    /**
     * Sets the HTML to be used with the css. This method should be used with setCss.
     *
     * @param string $html
     * @return void
     */
    public function set_html($html)
    {
        $this->html = $html;
    }
    /**
     * Sets the CSS to be merged with the HTML. This method should be used with setHtml.
     *
     * @param string $css
     * @return void
     */
    public function set_css($css)
    {
        $this->css = $css;
    }
    /**
     * Disables the parsing of <style> blocks.
     *
     * @return void
     */
    public function disable_style_blocks_parsing()
    {
        $this->disable_style_blocks_parsing = true;
    }
    /**
     * Processes the html by placing the css inline. Set first the css by using setCss and html by using setHtml.
     *
     * @return string
     * @throws \BadMethodCallException
     * @throws ParseException
     */
    public function process()
    {
        $emogrifier = Emogrifier_Css_Inliner::from_html($this->html);
        $emogrifier->set_debug($this->app_state->get_mode() === State::MODE_DEVELOPER);
        if ($this->disable_style_blocks_parsing) {
            $emogrifier->disable_style_blocks_parsing();
        }
        $emogrifier->inline_css($this->css);
        return $emogrifier->render();
    }
}