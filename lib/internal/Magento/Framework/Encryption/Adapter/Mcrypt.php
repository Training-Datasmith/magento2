<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Encryption\Adapter;

/**
 * Mcrypt adapter for decrypting values using legacy ciphers
 */
class Mcrypt implements Encryption_Adapter_Interface
{
    /**
     * @var string
     */
    private $cipher;
    /**
     * @var string
     */
    private $mode;
    /**
     * @var string
     */
    private $init_vector;
    /**
     * Encryption algorithm module handle
     *
     * @var resource
     */
    private $handle;
    /**
     * Mcrypt constructor.
     * @param string $key
     * @param string $cipher
     * @param string $mode
     * @param string $initVector
     * @throws \Exception
     */
    public function __construct(string $key, string $cipher = MCRYPT_BLOWFISH, string $mode = MCRYPT_MODE_ECB, ?string $init_vector = null)
    {
        $this->cipher = $cipher;
        $this->mode = $mode;
        // @codingStandardsIgnoreLine
        $this->handle = @mcrypt_module_open($cipher, '', $mode, '');
        try {
            // @codingStandardsIgnoreLine
            $max_key_size = @mcrypt_enc_get_key_size($this->handle);
            if (strlen($key) > $max_key_size) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Key must not exceed %1 bytes.', [$max_key_size]));
            }
            // @codingStandardsIgnoreLine
            $init_vector_size = @mcrypt_enc_get_iv_size($this->handle);
            if (null === $init_vector) {
                /* Set vector to zero bytes to not use it */
                $init_vector = str_repeat("\x00", $init_vector_size);
            } elseif (!is_string($init_vector) || strlen($init_vector) != $init_vector_size) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Init vector must be a string of %1 bytes.', [$init_vector_size]));
            }
            $this->init_vector = $init_vector;
        } catch (\Exception $e) {
            // @codingStandardsIgnoreLine
            @mcrypt_module_close($this->handle);
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase($e->get_message()));
        }
        // @codingStandardsIgnoreLine
        @mcrypt_generic_init($this->handle, $key, $init_vector);
    }
    /**
     * Destructor frees allocated resources
     */
    public function __destruct()
    {
        // @codingStandardsIgnoreStart
        @mcrypt_generic_deinit($this->handle);
        @mcrypt_module_close($this->handle);
        // @codingStandardsIgnoreEnd
    }
    /**
     * Retrieve a name of currently used cryptographic algorithm
     *
     * @return string
     */
    public function get_cipher(): string
    {
        return $this->cipher;
    }
    /**
     * Mode in which cryptographic algorithm is running
     *
     * @return string
     */
    public function get_mode(): string
    {
        return $this->mode;
    }
    /**
     * Retrieve an actual value of initial vector that has been used to initialize a cipher
     *
     * @return string
     */
    public function get_init_vector(): ?string
    {
        return $this->init_vector;
    }
    /**
     * Get the current mcrypt handle
     *
     * @return resource
     */
    public function get_handle()
    {
        return $this->handle;
    }
    /**
     * Encrypt a string
     *
     * @param  string $data String to encrypt
     * @return string
     * @throws \Exception
     */
    public function encrypt(string $data): string
    {
        if (strlen($data) == 0) {
            return $data;
        }
        // @codingStandardsIgnoreLine
        return @mcrypt_generic($this->get_handle(), $data);
    }
    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        if (strlen($data) == 0) {
            return $data;
        }
        // @codingStandardsIgnoreLine
        $data = @mdecrypt_generic($this->handle, $data);
        /*
         * Returned string can in fact be longer than the unencrypted string due to the padding of the data
         * @link http://www.php.net/manual/en/function.mdecrypt-generic.php
         */
        $data = rtrim($data, "\x00");
        return $data;
    }
}