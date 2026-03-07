<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\FlagManager;

/**
 * Cron class for the Advanced Reporting signup process
 */
class SignUp
{
    public function __construct(
        private readonly Connector $connector,
        private readonly WriterInterface $configWriter,
        private readonly FlagManager $flagManager,
        /**
         * Reinitable Config Model.
         */
        private readonly ReinitableConfigInterface $reinitableConfig
    ) {
    }

    /**
     * Execute scheduled subscription operation.
     *
     * In case of failure writes message to notifications inbox
     *
     * @throws NotFoundException
     */
    public function execute(): bool
    {
        $attemptsCount = (int)$this->flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);

        if ($attemptsCount <= 0) {
            $this->deleteAnalyticsCronExpr();
            $this->flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
            return false;
        }

        $attemptsCount--;
        $this->flagManager->saveFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $signUpResult = $this->connector->execute('signUp');
        if ($signUpResult === false) {
            return false;
        }

        $this->deleteAnalyticsCronExpr();
        $this->flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        return true;
    }

    /**
     * Delete cron schedule setting into config.
     *
     * Delete cron schedule setting for subscription handler into config and
     * re-initialize config cache to avoid auto-generate new schedule items.
     */
    private function deleteAnalyticsCronExpr(): bool
    {
        $this->configWriter->delete(SubscriptionHandler::CRON_STRING_PATH);
        $this->reinitableConfig->reinit();
        return true;
    }
}
