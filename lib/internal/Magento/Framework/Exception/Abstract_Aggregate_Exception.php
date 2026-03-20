<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
/**
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Aggregate_Exception extends Localized_Exception implements Aggregate_Exception_Interface
{
    /**
     * The array of errors that have been added via the addError() method
     *
     * @var \Magento\Framework\Exception\LocalizedException[]
     */
    protected $errors = [];
    /**
     * The original phrase
     *
     * @var \Magento\Framework\Phrase
     */
    protected $original_phrase;
    /**
     * An internal variable indicating how many time addError has been called
     *
     * @var int
     */
    private $add_error_calls = 0;
    /**
     * Initialize the exception
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase, ?\Exception $cause = null, $code = 0)
    {
        $this->original_phrase = $phrase;
        parent::__construct($phrase, $cause, $code);
    }
    /**
     * Add new error into the list of exceptions
     *
     * @param \Magento\Framework\Phrase $phrase
     * @return $this
     */
    public function add_error(Phrase $phrase)
    {
        $this->add_error_calls++;
        if (empty($this->errors)) {
            if (1 === $this->add_error_calls) {
                // First call: simply overwrite the phrase and message
                $this->phrase = $phrase;
                $this->message = $phrase->render();
                $this->log_message = null;
            } elseif (2 === $this->add_error_calls) {
                // Second call: store the error from the first call and the second call in the array
                // restore the phrase to its original value
                $this->errors[] = new Localized_Exception($this->phrase);
                $this->errors[] = new Localized_Exception($phrase);
                $this->phrase = $this->original_phrase;
                $this->message = $this->original_phrase->render();
                $this->log_message = null;
            }
        } else {
            // All subsequent calls after the second should reach here
            $this->errors[] = new Localized_Exception($phrase);
        }
        return $this;
    }
    /**
     * @param LocalizedException $exception
     * @return $this
     * @since 101.0.6
     */
    public function add_exception(Localized_Exception $exception)
    {
        $this->add_error_calls++;
        $this->errors[] = $exception;
        return $this;
    }
    /**
     * Should return true if someone has added different errors to this exception after construction
     *
     * @return bool
     */
    public function was_error_added()
    {
        return 0 < $this->add_error_calls;
    }
    /**
     * @inheritdoc
     */
    public function get_errors()
    {
        return $this->errors;
    }
}