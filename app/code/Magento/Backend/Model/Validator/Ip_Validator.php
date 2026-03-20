<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Validator;

use Magento\Framework\App\Utility\Ip_Address;
/**
 * Class to validate list of IPs for maintenance commands
 */
class Ip_Validator
{
    /**
     * @var string[]
     */
    private $none;
    /**
     * @var string[]
     */
    private $valid_ips;
    /**
     * @var string[]
     */
    private $invalid_ips;
    /**
     * @param IPAddress $ipAddress
     */
    public function __construct(private readonly Ip_Address $ip_address)
    {
    }
    /**
     * Validates list of ips
     *
     * @param string[] $ips
     * @param bool $noneAllowed
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate_ips(array $ips, $none_allowed)
    {
        $this->none = [];
        $this->valid_ips = [];
        $this->invalid_ips = [];
        $messages = [];
        $this->filter_ips($ips);
        if (count($this->none) > 0 && !$none_allowed) {
            $messages[] = "'none' is not allowed";
        } elseif ($none_allowed && count($this->none) > 1) {
            $messages[] = "'none' can be only used once";
        } elseif ($none_allowed && count($this->none) > 0 && (count($this->valid_ips) > 0 || count($this->invalid_ips) > 0)) {
            $messages[] = "Multiple values are not allowed when 'none' is used";
        } else {
            foreach ($this->invalid_ips as $invalid_ip) {
                $messages[] = "Invalid IP {$invalid_ip}";
            }
        }
        return $messages;
    }
    /**
     * Filter ips into 'none', valid and invalid ips
     *
     * @param string[] $ips
     *
     * @return void
     */
    private function filter_ips(array $ips)
    {
        foreach ($ips as $ip) {
            if ($ip === 'none') {
                $this->none[] = $ip;
            } elseif ($this->ip_address->is_valid_address($ip)) {
                $this->valid_ips[] = $ip;
            } elseif ($this->ip_address->is_valid_range($ip)) {
                $this->valid_ips[] = $ip;
            } else {
                $this->invalid_ips[] = $ip;
            }
        }
    }
}