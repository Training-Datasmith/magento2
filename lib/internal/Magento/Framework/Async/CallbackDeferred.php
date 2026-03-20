<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Async;

/**
 * Executes given callback when get() is used.
 */
class Callback_Deferred implements Cancelable_Deferred_Interface
{
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var bool
     */
    private $canceled = false;
    /**
     * @var bool
     */
    private $done = false;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var \Throwable
     */
    private $exception;
    /**
     * CallbackDeferred constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    /**
     * @inheritDoc
     */
    public function cancel(bool $force = false): void
    {
        if ($this->is_done()) {
            throw new Canceling_Deferred_Exception('Already executed');
        }
        if ($this->is_cancelled()) {
            throw new Canceling_Deferred_Exception('Already canceled');
        }
        $this->canceled = true;
    }
    /**
     * @inheritDoc
     */
    public function is_cancelled(): bool
    {
        return $this->canceled;
    }
    /**
     * Return deferred value.
     *
     * @return mixed
     * @throws \Throwable
     */
    private function return_results()
    {
        if ($this->exception) {
            throw $this->exception;
        }
        return $this->value;
    }
    /**
     * @inheritDoc
     */
    public function get()
    {
        if ($this->is_cancelled()) {
            throw new Canceling_Deferred_Exception('Deferred operation is canceled');
        }
        if (!$this->is_done()) {
            try {
                $this->value = ($this->callback)();
            } catch (\Throwable $exception) {
                $this->exception = $exception;
            }
            $this->done = true;
        }
        return $this->return_results();
    }
    /**
     * @inheritDoc
     */
    public function is_done(): bool
    {
        return $this->done;
    }
}