<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue\Consumer\Config;

/**
 * Composite validator for consumer config.
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($configData);
        }
    }
}
