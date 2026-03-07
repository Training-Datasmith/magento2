<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterface;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Output\ConsoleOutput;

class Consumer
{
    /**
     * @var Save
     */
    private $save;

    public function __construct(
        /**
         * Backend Config Model Factory
         */
        private readonly Factory $configFactory,
        private readonly Json $serializer,
        private readonly ScopeInterface $scope,
        private readonly ConsoleOutput $output
    ) {
        $this->scope->setCurrentScope('adminhtml');
        $this->save = ObjectManager::getInstance()->get(Save::class);
        $this->scope->setCurrentScope('global');
    }
    /**
     * Process Consumer
     *
     * @throws \Exception
     */
    public function process(AsyncConfigMessageInterface $asyncConfigMessage): void
    {
        $configData = $asyncConfigMessage->getConfigData();
        $data = $this->serializer->unserialize($configData);
        $data = $this->save->filterNodes($data);
        /** @var \Magento\Config\Model\Config $configModel */
        $configModel = $this->configFactory->create(['data' => $data]);
        try {
            $configModel->save();
        } catch (LocalizedException $exception) {
            $message = $exception->getMessage();
            $this->output->writeln(' Config couldn\'t be saved: ' . $message);
        }
    }
}
