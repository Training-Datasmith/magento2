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
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Command for setting allowed IPs in maintenance mode
 */
class Maintenance_Allow_Ips_Command extends Abstract_Setup_Command
{
    /**
     * Names of input arguments or options
     */
    public const INPUT_KEY_IP = 'ip';
    public const INPUT_KEY_NONE = 'none';
    public const INPUT_KEY_ADD = 'add';
    public const NAME = 'maintenance:allow-ips';
    /**
     * @var MaintenanceMode
     */
    private $maintenance_mode;
    /**
     * @var IpValidator
     */
    private $ip_validator;
    /**
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
    protected function configure(): void
    {
        $arguments = [new Input_Argument(self::INPUT_KEY_IP, Input_Argument::OPTIONAL | Input_Argument::IS_ARRAY, 'Allowed IP addresses')];
        $options = [new Input_Option(self::INPUT_KEY_NONE, null, Input_Option::VALUE_NONE, 'Clear allowed IP addresses'), new Input_Option(self::INPUT_KEY_ADD, null, Input_Option::VALUE_NONE, 'Add the IP address to existing list')];
        $this->set_name(self::NAME)->set_description('Sets maintenance mode exempt IPs')->set_definition(array_merge($arguments, $options));
        parent::configure();
    }
    /**
     * @inheritDoc
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        if (!$input->get_option(self::INPUT_KEY_NONE)) {
            $addresses = $input->get_argument(self::INPUT_KEY_IP);
            $messages = $this->validate($addresses);
            if (!empty($messages)) {
                $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $messages));
                // we must have an exit code higher than zero to indicate something was wrong
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
            if (!empty($addresses)) {
                if ($input->get_option(self::INPUT_KEY_ADD)) {
                    $addresses = array_unique(array_merge($this->maintenance_mode->get_address_info(), $addresses));
                }
                $this->maintenance_mode->set_addresses(implode(',', $addresses));
                $output->writeln('<info>Set exempt IP-addresses: ' . implode(' ', $this->maintenance_mode->get_address_info()) . '</info>');
            }
        } else {
            $this->maintenance_mode->set_addresses('');
            $output->writeln('<info>Set exempt IP-addresses: none</info>');
        }
        return Cli::RETURN_SUCCESS;
    }
    /**
     * Validates IP addresses and return error messages
     *
     * @param string[] $addresses
     * @return string[]
     */
    protected function validate(array $addresses): array
    {
        return $this->ip_validator->validate_ips($addresses, false);
    }
}