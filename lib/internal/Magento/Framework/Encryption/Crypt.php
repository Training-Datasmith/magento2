<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Encryption;

use Magento\Framework\Encryption\Adapter\Mcrypt;
/**
 * Class encapsulates cryptographic algorithm
 *
 * @api
 * @deprecated 102.0.0
 * @since 100.0.2
 */
class Crypt
{
    /**
     * @var string
     */
    protected $_cipher;
    /**
     * @var string
     */
    protected $_mode;
    /**
     * @var string
     */
    protected $_init_vector;
    /**
     * Mcrypt adapter
     *
     * @var Mcrypt
     */
    private $mcrypt;
    /**
     * Constructor
     *
     * @param string $key Secret encryption key.
     *                    It's unsafe to store encryption key in memory, so no getter for key exists.
     * @param string $cipher Cipher algorithm (one of the MCRYPT_ciphername constants)
     * @param string $mode Mode of cipher algorithm (MCRYPT_MODE_modeabbr constants)
     * @param string|bool $initVector Initial vector to fill algorithm blocks.
     *                                TRUE generates a random initial vector.
     *                                FALSE fills initial vector with zero bytes to not use it.
     * @throws \Exception
     * phpcs:disable PHPCompatibility.Constants.RemovedConstants
     */
    public function __construct($key, $cipher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_ECB, $init_vector = false)
    {
        if (true === $init_vector) {
            //phpcs:disable
            $handle = @mcrypt_module_open($cipher, '', $mode, '');
            $init_vector_size = @mcrypt_enc_get_iv_size($handle);
            //phpcs:enable
            /* Generate a random vector from human-readable characters */
            $allowed_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $init_vector = '';
            for ($i = 0; $i < $init_vector_size; $i++) {
                $init_vector .= $allowed_characters[random_int(0, strlen($allowed_characters) - 1)];
            }
            //phpcs:disable
            @mcrypt_generic_deinit($handle);
            @mcrypt_module_close($handle);
            //phpcs:enable
        }
        $this->mcrypt = new Mcrypt($key, $cipher, $mode, $init_vector === false ? null : $init_vector);
    }
    /**
     * Retrieve a name of currently used cryptographic algorithm
     *
     * @return string
     */
    public function get_cipher()
    {
        return $this->mcrypt->get_cipher();
    }
    /**
     * Mode in which cryptographic algorithm is running
     *
     * @return string
     */
    public function get_mode()
    {
        return $this->mcrypt->get_mode();
    }
    /**
     * Retrieve an actual value of initial vector that has been used to initialize a cipher
     *
     * @return string
     */
    public function get_init_vector()
    {
        return $this->mcrypt->get_init_vector();
    }
    /**
     * Encrypt a data
     *
     * @param  string $data String to encrypt
     * @return string
     */
    public function encrypt($data)
    {
        if (!$data || strlen($data) == 0) {
            return $data;
        }
        // @codingStandardsIgnoreLine
        return @mcrypt_generic($this->mcrypt->get_handle(), $data);
    }
    /**
     * Decrypt a data
     *
     * @param  string $data String to decrypt
     * @return string
     */
    public function decrypt($data)
    {
        return $this->mcrypt->decrypt($data);
    }
}