<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * An error handler that converts runtime errors into exceptions
 */
class Error_Handler
{
    /**
     * Error messages
     *
     * @var array
     */
    protected $error_phrases = [E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parse Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_RECOVERABLE_ERROR => 'Recoverable Error', E_DEPRECATED => 'Deprecated Functionality', E_USER_DEPRECATED => 'User Deprecated Functionality'];
    /**
     * Custom error handler
     *
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param int $errorLine
     * @return bool
     * @throws \Exception
     */
    public function handler($error_no, $error_str, $error_file, $error_line)
    {
        if ($error_str !== null && strpos($error_str, 'DateTimeZone::__construct') !== false) {
            // there's no way to distinguish between caught system exceptions and warnings
            return false;
        }
        $error_no = $error_no & error_reporting();
        if ($error_no == 0) {
            return false;
        }
        $msg = isset($this->error_phrases[$error_no]) ? $this->error_phrases[$error_no] : "Unknown error ({$error_no})";
        $msg .= ": {$error_str} in {$error_file} on line {$error_line}";
        // phpcs:ignore Magento2.Exceptions.DirectThrow
        throw new \Exception($msg);
    }
}