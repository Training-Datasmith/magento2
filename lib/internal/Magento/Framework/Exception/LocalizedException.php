<?php

declare (strict_types=1);
/**
 * Localized Exception
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
/**
 * Localized exception
 *
 * @api
 * @since 100.0.2
 */
class Localized_Exception extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase
     */
    protected $phrase;
    /**
     * @var string
     */
    protected $log_message;
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase, ?\Exception $cause = null, $code = 0)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), (int) $code, $cause);
    }
    /**
     * Get the un-processed message, without the parameters filled in
     *
     * @return string
     */
    public function get_raw_message()
    {
        return $this->phrase->get_text();
    }
    /**
     * Get parameters, corresponding to placeholders in raw exception message
     *
     * @return array
     */
    public function get_parameters()
    {
        return $this->phrase->get_arguments();
    }
    /**
     * Get the un-localized message, but with the parameters filled in
     *
     * @return string
     */
    public function get_log_message()
    {
        if ($this->log_message === null) {
            $renderer = new Placeholder();
            $this->log_message = $renderer->render([$this->get_raw_message()], $this->get_parameters());
        }
        return $this->log_message;
    }
}