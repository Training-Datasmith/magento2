<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Data;

use Magento\Framework\Api\AttributeValue;
use PHPUnit\Framework\TestCase;

class AttributeValueTest extends TestCase
{
    public const ATTRIBUTE_CODE = 'ATTRIBUTE_CODE';

    public const STRING_VALUE = 'VALUE';

    public const INTEGER_VALUE = 1;

    public const FLOAT_VALUE = 1.0;

    public const BOOLEAN_VALUE = true;

    public function testConstructorAndGettersWithString()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::STRING_VALUE,
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::STRING_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithInteger()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::INTEGER_VALUE,
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::INTEGER_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithFloat()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::FLOAT_VALUE,
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::FLOAT_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithBoolean()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::BOOLEAN_VALUE,
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::BOOLEAN_VALUE, $attribute->getValue());
    }
}
