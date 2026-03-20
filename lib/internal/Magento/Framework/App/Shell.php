<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
use Magento\Framework\Shell\Command_Renderer_Interface;
use Magento\Framework\Shell\Driver;
use Magento\Framework\Shell_Interface;
use Psr\Log\Logger_Interface;
/**
 * Class is separate from \Magento|Framework\Shell because logging behavior is different, and relies on ObjectManager
 * being available.
 */
class Shell implements Shell_Interface
{
    /**
     * @var \Magento\Framework\Shell\Driver
     */
    private $driver;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @param Driver $driver
     * @param CommandRendererInterface $commandRenderer
     * @param LoggerInterface $logger
     */
    public function __construct(Driver $driver, Logger_Interface $logger)
    {
        $this->driver = $driver;
        $this->logger = $logger;
    }
    /**
     * Execute a command through the command line, passing properly escaped arguments
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @throws \Magento\Framework\Exception\LocalizedException If a command returns non-zero exit code
     * @return string
     */
    public function execute($command, array $arguments = [])
    {
        try {
            $response = $this->driver->execute($command, $arguments);
        } catch (Localized_Exception $e) {
            $this->logger->error($e->get_log_message());
            throw $e;
        }
        $escaped_command = $response->get_escaped_command();
        $output = $response->get_output();
        $exit_code = $response->get_exit_code();
        $log_entry = $escaped_command . PHP_EOL . $output;
        if ($exit_code) {
            $this->logger->error($log_entry);
            $command_error = new \Exception($output, $exit_code);
            throw new Localized_Exception(new Phrase("Command returned non-zero exit code:\n`%1`", [$command]), $command_error);
        }
        $this->logger->info($log_entry);
        return $output;
    }
}