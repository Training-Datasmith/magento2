<?php

declare(strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_for_search_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_boolean_attribute_rollback.php');
