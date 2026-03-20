<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Use sequence of validators to validate requests.
 */
class Composite_Validator implements Validator_Interface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;
    /**
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }
    /**
     * @inheritDoc
     */
    public function validate(Request_Interface $request, Action_Interface $action): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($request, $action);
        }
    }
}