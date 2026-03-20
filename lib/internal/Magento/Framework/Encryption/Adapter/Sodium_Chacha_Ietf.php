<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Encryption\Adapter;

/**
 * Sodium adapter for encrypting and decrypting strings
 */
class Sodium_Chacha_Ietf implements Encryption_Adapter_Interface
{
    /**
     * @var string
     */
    private $key;
    /**
     * Sodium constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string string
     * @throws \SodiumException
     */
    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
        $cipher_text = sodium_crypto_aead_chacha20poly1305_ietf_encrypt((string) $data, $nonce, $nonce, $this->key);
        return $nonce . $cipher_text;
    }
    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
        $payload = mb_substr($data, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');
        try {
            $plain_text = sodium_crypto_aead_chacha20poly1305_ietf_decrypt($payload, $nonce, $nonce, $this->key);
        } catch (\Sodium_Exception $e) {
            $plain_text = '';
        }
        return $plain_text !== false ? $plain_text : '';
    }
}