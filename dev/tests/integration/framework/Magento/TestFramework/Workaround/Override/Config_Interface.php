<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override;

use PHPUnit\Framework\TestCase;

/**
 * Provides integration tests configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
interface ConfigInterface
{
    /**
     * Returns an array with skip key and skipMessage key if test is skipped.
     *
     * @param TestCase $test
     * @return array
     */
    public function getSkipConfiguration(TestCase $test): array;

    /**
     * Test has configuration flag.
     *
     * @param string $className
     * @return bool
     */
    public function hasSkippedTest(string $className): bool;

    /**
     * Get config from class node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getClassConfig(TestCase $test, ?string $fixtureType = null): array;

    /**
     * Get config from method node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getMethodConfig(TestCase $test, ?string $fixtureType = null): array;

    /**
     * Get config from dataSet node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getDataSetConfig(TestCase $test, ?string $fixtureType = null): array;
}
