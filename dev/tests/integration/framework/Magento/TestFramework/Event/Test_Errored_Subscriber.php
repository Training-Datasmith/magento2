<?php

declare(strict_types=1);
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Test Errored Subscriber
 */

namespace Magento\TestFramework\Event;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;

class TestErroredSubscriber implements ErroredSubscriber
{
    /**
     * Errored Subscriber
     *
     * @param Errored $event
     */
    public function notify(Errored $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();
        $objectManager = Bootstrap::getObjectManager();

        $testObj = $objectManager->create($className, ['name' => $methodName]);
        $phpUnit = $objectManager->create(PhpUnit::class);
        $phpUnit->endTest($testObj, 0);

        Magento::setCurrentEventObject(null);
        Magento::setTestPrepared(false);
    }
}
