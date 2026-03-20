<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception\Temporary_State;

use Magento\Framework\Exception\Could_Not_Save_Exception as LocalizedCouldNotSaveException;
use Magento\Framework\Exception\Temporary_State_Exception_Interface;
use Magento\Framework\Phrase;
/**
 * CouldNotSaveException caused by recoverable error
 *
 * @api
 * @since 101.0.0
 */
class Could_Not_Save_Exception extends Localized_Could_Not_Save_Exception implements Temporary_State_Exception_Interface
{
    /**
     * Class constructor
     *
     * @param Phrase $phrase The Exception message to throw.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining.
     * @param int $code [optional] The Exception code.
     */
    public function __construct(Phrase $phrase, ?\Exception $previous = null, $code = 0)
    {
        parent::__construct($phrase, $previous, $code);
        $this->code = $code;
    }
}