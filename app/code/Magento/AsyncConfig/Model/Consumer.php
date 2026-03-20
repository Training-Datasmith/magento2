<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Model;

use Magento\Async_Config\Api\Data\Async_Config_Message_Interface;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Scope_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Output\Console_Output;
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
        private readonly Factory $config_factory,
        private readonly Json $serializer,
        private readonly Scope_Interface $scope,
        private readonly Console_Output $output
    )
    {
        $this->scope->set_current_scope('adminhtml');
        $this->save = Object_Manager::get_instance()->get(Save::class);
        $this->scope->set_current_scope('global');
    }
    /**
     * Process Consumer
     *
     * @throws \Exception
     */
    public function process(Async_Config_Message_Interface $async_config_message): void
    {
        $config_data = $async_config_message->get_config_data();
        $data = $this->serializer->unserialize($config_data);
        $data = $this->save->filter_nodes($data);
        /** @var \Magento\Config\Model\Config $configModel */
        $config_model = $this->config_factory->create(['data' => $data]);
        try {
            $config_model->save();
        } catch (Localized_Exception $exception) {
            $message = $exception->get_message();
            $this->output->writeln(' Config couldn\'t be saved: ' . $message);
        }
    }
}