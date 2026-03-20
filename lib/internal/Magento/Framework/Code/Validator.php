<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code;

class Validator implements Validator_Interface
{
    /**
     * @var ValidatorInterface[]
     */
    protected $_validators = [];
    /**
     * Add validator
     *
     * @param ValidatorInterface $validator
     * @return void
     */
    public function add(Validator_Interface $validator)
    {
        $this->_validators[] = $validator;
    }
    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($class_name)
    {
        foreach ($this->_validators as $validator) {
            $validator->validate($class_name);
        }
    }
}