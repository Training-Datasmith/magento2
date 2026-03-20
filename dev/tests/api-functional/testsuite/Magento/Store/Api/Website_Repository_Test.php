<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for website repository interface.
 */
class WebsiteRepositoryTest extends WebapiAbstract
{
    public const SERVICE_NAME = 'storeWebsiteRepositoryV1';
    public const SERVICE_VERSION = 'V1';
    public const RESOURCE_PATH = '/V1/store/websites';

    /**
     * Test getList
     */
    public function testGetList()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = [];
        $websites = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($websites);
        $this->assertGreaterThan(1, count($websites));
        $keys = ['id', 'code', 'name', 'default_group_id'];
        $this->assertEquals($keys, array_keys($websites[0]));
    }
}
