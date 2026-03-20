<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Backend\Model\Validator\Ip_Validator;
use Magento\Framework\App\Maintenance_Mode;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\Abstract_Setup_Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * General maintenance command.
 */
abstract class Abstract_Maintenance_Command extends Abstract_Setup_Command
{
    /**
     * Names of input option
     */
    public const INPUT_KEY_IP = 'ip';
    /**
     * @var MaintenanceMode
     */
    protected $maintenance_mode;
    /**
     * @var IpValidator
     */
    protected $ip_validator;
    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     * @param IpValidator $ipValidator
     */
    public function __construct(Maintenance_Mode $maintenance_mode, Ip_Validator $ip_validator)
    {
        $this->maintenance_mode = $maintenance_mode;
        $this->ip_validator = $ip_validator;
        parent::__construct();
    }
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = [new Input_Option(self::INPUT_KEY_IP, null, Input_Option::VALUE_IS_ARRAY | Input_Option::VALUE_REQUIRED, "Allowed IP addresses (use 'none' to clear allowed IP list)")];
        $this->set_definition($options);
        parent::configure();
    }
    /**
     * Get maintenance mode to set
     *
     * @return bool
     */
    abstract protected function is_enable();
    /**
     * Get display string after mode is set
     *
     * @return string
     */
    abstract protected function get_display_string();
    /**
     * @inheritDoc
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $addresses = $input->get_option(self::INPUT_KEY_IP);
        $messages = $this->validate($addresses);
        if (!empty($messages)) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $messages));
            // We must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
        $this->maintenance_mode->set($this->is_enable());
        $output->writeln($this->get_display_string());
        if (!empty($addresses)) {
            $addresses = implode(',', $addresses);
            $addresses = 'none' === $addresses ? '' : $addresses;
            $this->maintenance_mode->set_addresses($addresses);
            $output->writeln('<info>Set exempt IP-addresses: ' . (implode(', ', $this->maintenance_mode->get_address_info()) ?: 'none') . '</info>');
        }
        return Cli::RETURN_SUCCESS;
    }
    /**
     * Validates IP addresses and return error messages
     *
     * @param string[] $addresses
     * @return string[]
     */
    protected function validate(array $addresses)
    {
        return $this->ip_validator->validate_ips($addresses, true);
    }
}