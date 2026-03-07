<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for encrypting data.
 */
class Cryptographer
{
    /**
     * Cipher method for encryption.
     */
    private string $cipherMethod = 'AES-256-CBC';

    /**
     * @var EncodedContextFactory
     */
    private $encodedContextFactory;

    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly AnalyticsToken $analyticsToken,
        EncodedContextFactory $encodedContextFactory
    ) {
        $this->encodedContextFactory = $encodedContextFactory;
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
                $source = (string)$source;
            } catch (\Exception) {
                throw new LocalizedException(
                    __(
                        'The data is invalid. '
                        . 'Enter the data as a string or data that can be converted into a string and try again.'
                    )
                );
            }
        } elseif (!$source) {
            throw new LocalizedException(__('The data is invalid. Enter the data as a string and try again.'));
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('The data is invalid. Use a valid cipher method and try again.'));
        }
        $initializationVector = $this->getInitializationVector();

        return $this->encodedContextFactory->create([
            'content' => openssl_encrypt(
                $source,
                $this->cipherMethod,
                $this->getKey(),
                OPENSSL_RAW_DATA,
                $initializationVector
            ),
            'initializationVector' => $initializationVector,
        ]);
    }

    /**
     * Return key for encryption.
     *
     * @throws LocalizedException
     */
    private function getKey(): string
    {
        $token = $this->analyticsToken->getToken();
        if (!$token) {
            throw new LocalizedException(__('Enter the encryption key and try again.'));
        }
        return hash('sha256', $token);
    }

    /**
     * Return established cipher method.
     *
     * @return string
     */
    private function getCipherMethod()
    {
        return $this->cipherMethod;
    }

    /**
     * Return each time generated random initialization vector which depends on the cipher method.
     */
    private function getInitializationVector(): string
    {
        $ivSize = openssl_cipher_iv_length($this->getCipherMethod());
        return openssl_random_pseudo_bytes($ivSize);
    }

    /**
     * Check that cipher method is allowed for encryption.
     *
     * @param string $cipherMethod
     */
    private function validateCipherMethod($cipherMethod): bool
    {
        $methods = array_map(
            strtolower(...),
            openssl_get_cipher_methods()
        );
        $cipherMethod = strtolower($cipherMethod);

        return (false !== array_search($cipherMethod, $methods));
    }
}
