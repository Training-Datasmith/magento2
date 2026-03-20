<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\App\Backpressure\Backpressure_Exceeded_Exception;
use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\App\Backpressure_Enforcer_Interface;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\Logger_Interface;
/**
 * Uses Sliding Window approach to record request times and enforce limits
 */
class Sliding_Window_Enforcer implements Backpressure_Enforcer_Interface
{
    /**
     * @var RequestLoggerFactoryInterface
     */
    private Request_Logger_Factory_Interface $request_logger_factory;
    /**
     * @var LimitConfigManagerInterface
     */
    private Limit_Config_Manager_Interface $config_manager;
    /**
     * @var DateTime
     */
    private DateTime $date_time;
    /**
     * @var DeploymentConfig
     */
    private Deployment_Config $deployment_config;
    /**
     * @var LoggerInterface
     */
    private Logger_Interface $logger;
    /**
     * @param RequestLoggerFactoryInterface $requestLoggerFactory
     * @param LimitConfigManagerInterface $configManager
     * @param DateTime $dateTime
     * @param DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(Request_Logger_Factory_Interface $request_logger_factory, Limit_Config_Manager_Interface $config_manager, DateTime $date_time, Deployment_Config $deployment_config, Logger_Interface $logger)
    {
        $this->request_logger_factory = $request_logger_factory;
        $this->config_manager = $config_manager;
        $this->date_time = $date_time;
        $this->deployment_config = $deployment_config;
        $this->logger = $logger;
    }
    /**
     * @inheritDoc
     *
     * @throws FileSystemException
     */
    public function enforce(Context_Interface $context): void
    {
        try {
            $request_logger = $this->get_request_logger();
            $limit = $this->config_manager->read_limit($context);
            $time = $this->date_time->gmt_timestamp();
            $remainder = $time % $limit->get_period();
            //Time slot is the ts of the beginning of the period
            $time_slot = $time - $remainder;
            $count = $request_logger->incr_and_get_for($context, $time_slot, $limit->get_period() * 3);
            if ($count <= $limit->get_limit()) {
                //Try to compare to a % of requests from previous time slot
                $prev_count = $request_logger->get_for($context, $time_slot - $limit->get_period());
                if ($prev_count != null) {
                    $count += $prev_count * (1 - $remainder / $limit->get_period());
                }
            }
            if ($count > $limit->get_limit()) {
                throw new Backpressure_Exceeded_Exception();
            }
        } catch (RuntimeException $e) {
            $this->logger->error('Backpressure sliding window not applied. ' . $e->get_message());
        }
    }
    /**
     * Returns request logger
     *
     * @return RequestLoggerInterface
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_request_logger(): Request_Logger_Interface
    {
        return $this->request_logger_factory->create((string) $this->deployment_config->get(Request_Logger_Interface::CONFIG_PATH_BACKPRESSURE_LOGGER));
    }
}