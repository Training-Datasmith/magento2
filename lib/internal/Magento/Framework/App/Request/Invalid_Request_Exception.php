<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Response_Interface;
use Magento\Framework\Controller\Result_Interface;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;
/**
 * Received request is invalid.
 *
 * @api
 */
class Invalid_Request_Exception extends RuntimeException
{
    /**
     * @var ResponseInterface|ResultInterface
     */
    private $replace_result;
    /**
     * @var Phrase[]|null
     */
    private $messages;
    /**
     * @param ResponseInterface|ResultInterface|NotFoundException $replaceResult
     * Use this result instead of calling an action instance,
     * if NotFoundException is given the default 404 mechanism will be triggered.
     * @param Phrase[]|null $messages Messages to show to client
     * as error messages.
     */
    public function __construct($replace_result, ?array $messages = null)
    {
        parent::__construct(new Phrase('Invalid request received'));
        $this->replace_result = $replace_result;
        $this->messages = $messages;
    }
    /**
     * Return replaced result
     *
     * @return ResponseInterface|ResultInterface|NotFoundException
     */
    public function get_replace_result()
    {
        return $this->replace_result;
    }
    /**
     * Return messages
     *
     * @return Phrase[]|null
     */
    public function get_messages(): ?array
    {
        return $this->messages;
    }
}