<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Validator\Url_Key;

/**
 * Class Composite validates if urlKey doesn't matches frontName or restricted words(endpoint names)
 */
class Composite_Url_Key implements Url_Key_Validator_Interface
{
    /**
     * @var UrlKeyValidatorInterface[]
     */
    private $validators;
    /**
     * @param array $validators
     */
    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }
    /**
     * @inheritDoc
     */
    public function validate(string $url_key): array
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $errors[] = $validator->validate($url_key);
        }
        return array_merge([], ...$errors);
    }
}