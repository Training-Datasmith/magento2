<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

/**
 * Validates paths using driver.
 */
class Composite_Path_Validator implements Path_Validator_Interface
{
    /**
     * @var PathValidatorInterface[]
     */
    private $validators;
    /**
     * @param PathValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }
    /**
     * @inheritDoc
     */
    public function validate(string $directory_path, string $path, ?string $scheme = null, bool $absolute_path = false): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($directory_path, $path, $scheme, $absolute_path);
        }
    }
}