<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Class for encrypting data.
 */
class Cryptographer
{
    /**
     * Cipher method for encryption.
     */
    private string $cipher_method = 'AES-256-CBC';
    /**
     * @var EncodedContextFactory
     */
    private $encoded_context_factory;
    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly Analytics_Token $analytics_token,
        Encoded_Context_Factory $encoded_context_factory
    )
    {
        $this->encoded_context_factory = $encoded_context_factory;
    }
    /**
     * Encrypt input data.
     *
     * @param string $source
     * @return EncodedContext
     * @throws LocalizedException
     */
    public function encode($source)
    {
        if (!is_string($source)) {
            try {
                $source = (string) $source;
            } catch (\Exception) {
                throw new Localized_Exception(__('The data is invalid. ' . 'Enter the data as a string or data that can be converted into a string and try again.'));
            }
        } elseif (!$source) {
            throw new Localized_Exception(__('The data is invalid. Enter the data as a string and try again.'));
        }
        if (!$this->validate_cipher_method($this->cipher_method)) {
            throw new Localized_Exception(__('The data is invalid. Use a valid cipher method and try again.'));
        }
        $initialization_vector = $this->get_initialization_vector();
        return $this->encoded_context_factory->create(['content' => openssl_encrypt($source, $this->cipher_method, $this->get_key(), OPENSSL_RAW_DATA, $initialization_vector), 'initializationVector' => $initialization_vector]);
    }
    /**
     * Return key for encryption.
     *
     * @throws LocalizedException
     */
    private function get_key(): string
    {
        $token = $this->analytics_token->get_token();
        if (!$token) {
            throw new Localized_Exception(__('Enter the encryption key and try again.'));
        }
        return hash('sha256', $token);
    }
    /**
     * Return established cipher method.
     *
     * @return string
     */
    private function get_cipher_method()
    {
        return $this->cipher_method;
    }
    /**
     * Return each time generated random initialization vector which depends on the cipher method.
     */
    private function get_initialization_vector(): string
    {
        $iv_size = openssl_cipher_iv_length($this->get_cipher_method());
        return openssl_random_pseudo_bytes($iv_size);
    }
    /**
     * Check that cipher method is allowed for encryption.
     *
     * @param string $cipherMethod
     */
    private function validate_cipher_method($cipher_method): bool
    {
        $methods = array_map(strtolower(...), openssl_get_cipher_methods());
        $cipher_method = strtolower($cipher_method);
        return false !== array_search($cipher_method, $methods);
    }
}