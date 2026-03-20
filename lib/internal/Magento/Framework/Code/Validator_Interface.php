<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Code;

/**
 * Interface \Magento\Framework\Code\ValidatorInterface
 *
 * @api
 */
interface Validator_Interface
{
    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($class_name);
}