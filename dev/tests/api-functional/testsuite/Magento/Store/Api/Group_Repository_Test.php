<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for (store) group repository interface.
 */
class GroupRepositoryTest extends WebapiAbstract
{
    public const SERVICE_NAME = 'storeGroupRepositoryV1';
    public const SERVICE_VERSION = 'V1';
    public const RESOURCE_PATH = '/V1/store/storeGroups';

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
        $storeGroups = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($storeGroups);
        $this->assertGreaterThan(1, count($storeGroups));
        $keys = ['id', 'website_id', 'root_category_id', 'default_store_id', 'name', 'code'];
        $this->assertEquals($keys, array_keys($storeGroups[0]));
    }
}
