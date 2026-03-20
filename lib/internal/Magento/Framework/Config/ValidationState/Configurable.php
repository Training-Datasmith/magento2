<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Config\Validation_State;

use Magento\Framework\Config\Validation_State_Interface;
/**
 * A configurable validation state
 */
class Configurable implements Validation_State_Interface
{
    /**
     * @var bool
     */
    private $required;
    /**
     * @param bool $required
     */
    public function __construct(bool $required)
    {
        $this->required = $required;
    }
    /**
     * @inheritdoc
     */
    public function is_validation_required(): bool
    {
        return $this->required;
    }
}