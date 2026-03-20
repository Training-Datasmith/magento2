<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
/**
 * Interpreter that aggregates named interpreters and delegates every evaluation to one of them
 */
class Composite implements Interpreter_Interface
{
    /**
     * Format: array('<name>' => <instance>, ...)
     *
     * @var InterpreterInterface[]
     */
    private $interpreters;
    /**
     * Data key that holds name of an interpreter to be used for that data
     *
     * @var string
     */
    private $discriminator;
    /**
     * @param InterpreterInterface[] $interpreters
     * @param string $discriminator
     * @throws \InvalidArgumentException
     */
    public function __construct(array $interpreters, $discriminator)
    {
        foreach ($interpreters as $interpreter_name => $interpreter_instance) {
            if (!$interpreter_instance instanceof Interpreter_Interface) {
                throw new \InvalidArgumentException("Interpreter named '{$interpreter_name}' is expected to be an argument interpreter instance.");
            }
        }
        $this->interpreters = $interpreters;
        $this->discriminator = $discriminator;
    }
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (!isset($data[$this->discriminator])) {
            throw new \InvalidArgumentException(sprintf('Value for key "%s" is missing in the argument data.', $this->discriminator));
        }
        $interpreter_name = $data[$this->discriminator];
        unset($data[$this->discriminator]);
        $interpreter = $this->get_interpreter($interpreter_name);
        return $interpreter->evaluate($data);
    }
    /**
     * Register interpreter instance under a given unique name
     *
     * @param string $name
     * @param InterpreterInterface $instance
     * @return void
     * @throws \InvalidArgumentException
     */
    public function add_interpreter($name, Interpreter_Interface $instance)
    {
        if (isset($this->interpreters[$name])) {
            throw new \InvalidArgumentException("Argument interpreter named '{$name}' has already been defined.");
        }
        $this->interpreters[$name] = $instance;
    }
    /**
     * Retrieve interpreter instance by its unique name
     *
     * @param string $name
     * @return InterpreterInterface
     * @throws \InvalidArgumentException
     */
    protected function get_interpreter($name)
    {
        if (!isset($this->interpreters[$name])) {
            throw new \InvalidArgumentException("Argument interpreter named '{$name}' has not been defined.");
        }
        return $this->interpreters[$name];
    }
}