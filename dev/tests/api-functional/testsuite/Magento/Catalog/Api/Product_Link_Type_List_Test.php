<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Api;

use Magento\Catalog\Model\Product\Link;

class ProductLinkTypeListTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    public const SERVICE_NAME = 'catalogProductLinkTypeListV1';
    public const SERVICE_VERSION = 'V1';
    public const RESOURCE_PATH = '/V1/products/';

    public function testGetItems()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'links/types',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItems',
            ],
        ];
        $actual = $this->_webApiCall($serviceInfo);
        $expectedItems = ['name' => 'related', 'code' => Link::LINK_TYPE_RELATED];
        $this->assertContainsEquals($expectedItems, $actual);
    }

    public function testGetItemAttributes()
    {
        $linkType = 'related';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'links/' . $linkType . '/attributes',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItemAttributes',
            ],
        ];
        $actual = $this->_webApiCall($serviceInfo, ['type' => $linkType]);
        $expected = [['code' => 'position', 'type' => 'int']];
        $this->assertEquals($expected, $actual);
    }
}
