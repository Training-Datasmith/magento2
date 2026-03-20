<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Console;

/**
 * HTTP response implementation.
 */
class Response implements \Magento\Framework\App\Response_Interface
{
    /**
     * Status code
     * Possible values:
     *  0 (successfully)
     *  1-255 (error)
     *  -1 (error)
     *
     * @var int
     */
    protected $code = 0;
    /**
     * Success code
     */
    public const SUCCESS = 0;
    /**
     * Error code
     */
    public const ERROR = 255;
    /**
     * Text to output on send response
     *
     * @var string
     */
    private $body;
    /**
     * Set whether to terminate process on send or not
     *
     * @var bool
     */
    protected $terminate_on_send = true;
    /**
     * Send response to client
     *
     * @return int
     */
    public function send_response()
    {
        if (!empty($this->body)) {
            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            echo $this->body;
        }
        if ($this->terminate_on_send) {
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit($this->code);
        }
        return $this->code;
    }
    /**
     * Get body
     *
     * @return string
     */
    public function get_body()
    {
        return $this->body;
    }
    /**
     * Set body
     *
     * @param string $body
     * @return void
     */
    public function set_body($body)
    {
        $this->body = $body;
    }
    /**
     * Set exit code
     *
     * @param int $code
     * @return void
     */
    public function set_code($code)
    {
        if ($code > 255) {
            $code = 255;
        }
        $this->code = $code;
    }
    /**
     * Set whether to terminate process on send or not
     *
     * @param bool $terminate
     * @return void
     */
    public function terminate_on_send($terminate)
    {
        $this->terminate_on_send = $terminate;
    }
}