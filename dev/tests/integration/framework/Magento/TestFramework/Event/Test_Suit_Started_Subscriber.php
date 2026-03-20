<?php

declare(strict_types=1);
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * TestSuite Started Subscriber
 */

namespace Magento\TestFramework\Event;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use PHPUnit\Framework\TestSuite;

class TestSuitStartedSubscriber implements StartedSubscriber
{
    /**
     * TestSuit Started Subscriber
     *
     * @param Started $event
     */
    public function notify(Started $event): void
    {
        $phpUnit = Bootstrap::getObjectManager()->create(PhpUnit::class);
        if (class_exists($event->testSuite()->name())) {
            $testSuite = TestSuite::empty($event->testSuite()->name());
            $phpUnit->startTestSuite($testSuite);
        }
    }
}
