<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Event\Manager;
use Magento\Framework\Filesystem;
/**
 * Application Maintenance Mode
 */
class Maintenance_Mode
{
    /**
     * Maintenance flag file name
     *
     * DO NOT consolidate this file and the IP allow list into one.
     * It is going to work much faster in 99% of cases: the isOn() will return false whenever file doesn't exist.
     */
    public const FLAG_FILENAME = '.maintenance.flag';
    /**
     * IP-addresses file name
     */
    public const IP_FILENAME = '.maintenance.ip';
    /**
     * Maintenance flag dir
     */
    public const FLAG_DIR = Directory_List::VAR_DIR;
    /**
     * Path to store files
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $flag_dir;
    /**
     * @var Manager
     */
    private $event_manager;
    /**
     * @param Filesystem $filesystem
     * @param Utility\IPAddress $ipAddress
     * @param Manager|null $eventManager
     */
    public function __construct(Filesystem $filesystem, private readonly Utility\Ip_Address $ip_address, ?Manager $event_manager = null)
    {
        $this->flag_dir = $filesystem->get_directory_write(self::FLAG_DIR);
        $this->event_manager = $event_manager ?: Object_Manager::get_instance()->get(Manager::class);
    }
    /**
     * Checks whether mode is on
     *
     * Optionally specify an IP-address to compare against the allow list
     *
     * @param string $remoteAddr
     *
     * @return bool
     */
    public function is_on($remote_addr = '')
    {
        if (!$this->flag_dir->is_exist(self::FLAG_FILENAME)) {
            return false;
        }
        if ($remote_addr) {
            $allowed_addresses = $this->get_address_info();
            foreach ($allowed_addresses as $allowed) {
                if ($allowed === $remote_addr) {
                    return false;
                }
                if (!$this->ip_address->is_valid_range($allowed)) {
                    continue;
                }
                if ($this->ip_address->range_contains_address($allowed, $remote_addr)) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Sets maintenance mode "on" or "off"
     *
     * @param bool $isOn
     *
     * @return bool
     */
    public function set($is_on)
    {
        $this->event_manager->dispatch('maintenance_mode_changed', ['isOn' => $is_on]);
        if ($is_on) {
            return $this->flag_dir->touch(self::FLAG_FILENAME);
        }
        if ($this->flag_dir->is_exist(self::FLAG_FILENAME)) {
            return $this->flag_dir->delete(self::FLAG_FILENAME);
        }
        return true;
    }
    /**
     * Sets list of allowed IP addresses
     *
     * @param string $addresses
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function set_addresses($addresses)
    {
        $addresses = (string) $addresses;
        if (empty($addresses)) {
            if ($this->flag_dir->is_exist(self::IP_FILENAME)) {
                return $this->flag_dir->delete(self::IP_FILENAME);
            }
            return true;
        }
        if (!preg_match('/^[^\s,]+(,[^\s,]+)*$/', $addresses)) {
            throw new \InvalidArgumentException("One or more IP-addresses is expected (comma-separated)\n");
        }
        $result = $this->flag_dir->write_file(self::IP_FILENAME, $addresses);
        return false !== $result;
    }
    /**
     * Get list of IP addresses effective for maintenance mode
     *
     * @return string[]
     */
    public function get_address_info()
    {
        if ($this->flag_dir->is_exist(self::IP_FILENAME)) {
            $temp = $this->flag_dir->read_file(self::IP_FILENAME);
            return explode(',', trim($temp));
        } else {
            return [];
        }
    }
}