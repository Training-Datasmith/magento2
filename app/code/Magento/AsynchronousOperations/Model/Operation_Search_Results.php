<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Search_Results_Interface;
use Magento\Framework\Api\Search_Results;
/**
 * Service Data Object with bulk Operation search result.
 */
class Operation_Search_Results extends Search_Results implements Operation_Search_Results_Interface
{
}