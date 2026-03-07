<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Code\Test\Unit;

use Magento\Framework\Code\Validator;
use Magento\Framework\Code\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Validator();
    }

    public function testValidate()
    {
        $className = 'Same\Class\Name';
        $validator1 = $this->createMock(ValidatorInterface::class);
        $validator1->expects($this->once())->method('validate')->with($className);
        $validator2 = $this->createMock(ValidatorInterface::class);
        $validator2->expects($this->once())->method('validate')->with($className);

        $this->model->add($validator1);
        $this->model->add($validator2);
        $this->model->validate($className);
    }
}
