<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Utility;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
class Ip_Address
{
    /**
     * Returns true if the supplied string is a valid IPv4 or IPv6 address
     *
     * @param string $address
     *
     * @return bool
     */
    public function is_valid_address(string $address): bool
    {
        return $address === filter_var($address, FILTER_VALIDATE_IP);
    }
    /**
     * Returns true if the supplied string is a valid IP address range in CIDR notation
     *
     * @param string $range
     *
     * @return bool
     */
    public function is_valid_range(string $range): bool
    {
        if (!str_contains($range, '/')) {
            return false;
        }
        [$network, $mask] = explode('/', $range, 2);
        $max_length = str_contains($network, ':') ? 128 : 32;
        if (!is_numeric($mask) || $mask < 0 || $mask > $max_length) {
            return false;
        }
        return $this->is_valid_address($network);
    }
    /**
     * Returns true if the supplied address is included within the supplied range
     *
     * @param string $range
     * @param string $address
     *
     * @return bool
     *
     * @throws LocalizedException
     */
    public function range_contains_address(string $range, string $address): bool
    {
        if (!$this->is_valid_range($range)) {
            throw new Localized_Exception(new Phrase('Invalid range provided: %1', [$range]));
        }
        if (!$this->is_valid_address($address)) {
            throw new Localized_Exception(new Phrase('Invalid address provided: %1', [$address]));
        }
        [$network, $mask] = explode('/', $range, 2);
        $mask = (int) $mask;
        return substr($this->ip_address_to_binary_string($network), 0, $mask) === substr($this->ip_address_to_binary_string($address), 0, $mask);
    }
    /**
     * Returns the binary representation of an IP address as a string
     *
     * @param string $address
     *
     * @return string
     */
    private function ip_address_to_binary_string(string $address): string
    {
        $binary = '';
        foreach (unpack('C*', inet_pton($address)) as $byte) {
            $binary .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }
        return $binary;
    }
}