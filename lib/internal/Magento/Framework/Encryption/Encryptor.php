<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Encryption;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Encryption\Adapter\Encryption_Adapter_Interface;
use Magento\Framework\Encryption\Adapter\Mcrypt;
use Magento\Framework\Encryption\Adapter\Sodium_Chacha_Ietf;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Math\Random;
/**
 * Class Encryptor provides basic logic for hashing strings and encrypting/decrypting misc data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Encryptor implements Encryptor_Interface
{
    /**
     * Key of md5 algorithm
     */
    public const HASH_VERSION_MD5 = 0;
    /**
     * Key of sha256 algorithm
     */
    public const HASH_VERSION_SHA256 = 1;
    /**
     * Key of Argon2ID13 algorithm
     */
    public const HASH_VERSION_ARGON2ID13 = 2;
    /**
     * Key of Argon2ID13 algorithm that works on any PHP and libsodium version.
     */
    public const HASH_VERSION_ARGON2ID13_AGNOSTIC = 3;
    /**
     * Key of latest used algorithm
     *
     * @deprecated Latest version is dynamic based on current setup.
     * @see \Magento\Framework\Encryption\Encryptor::getLatestHashVersion
     */
    public const HASH_VERSION_LATEST = 3;
    /**
     * Default length of salt in bytes
     */
    public const DEFAULT_SALT_LENGTH = 32;
    /**#@+
     * Exploded password hash keys
     */
    public const PASSWORD_HASH = 0;
    public const PASSWORD_SALT = 1;
    public const PASSWORD_VERSION = 2;
    /**#@-*/
    /**
     * Array key of encryption key in deployment config
     */
    public const PARAM_CRYPT_KEY = 'crypt/key';
    /**#@+
     * Cipher versions
     */
    public const CIPHER_BLOWFISH = 0;
    public const CIPHER_RIJNDAEL_128 = 1;
    public const CIPHER_RIJNDAEL_256 = 2;
    public const CIPHER_AEAD_CHACHA20POLY1305 = 3;
    public const CIPHER_LATEST = 3;
    /**#@-*/
    /**
     * Default hash string delimiter
     */
    public const DELIMITER = ':';
    /**
     * Map of simple hash versions
     *
     * @var array
     */
    private $hash_version_map = [self::HASH_VERSION_MD5 => 'md5', self::HASH_VERSION_SHA256 => 'sha256'];
    /**
     * Indicate cipher
     *
     * @var int
     */
    protected $cipher = self::CIPHER_LATEST;
    /**
     * Version of encryption key
     *
     * @var int
     */
    protected $key_version;
    /**
     * Array of encryption keys
     *
     * @var string[]
     */
    protected $keys = [];
    /**
     * @var Random
     */
    private $random;
    /**
     * @var KeyValidator
     */
    private $key_validator;
    /**
     * Encryptor constructor.
     *
     * @param Random $random
     * @param DeploymentConfig $deploymentConfig
     * @param KeyValidator|null $keyValidator
     */
    public function __construct(Random $random, Deployment_Config $deployment_config, ?Key_Validator $key_validator = null)
    {
        $this->random = $random;
        // load all possible keys
        $this->keys = preg_split('/\s+/s', trim((string) $deployment_config->get(self::PARAM_CRYPT_KEY)));
        $this->key_version = count($this->keys) - 1;
        $this->key_validator = $key_validator ?: Object_Manager::get_instance()->get(Key_Validator::class);
    }
    /**
     * Gets latest hash algorithm version.
     *
     * @return int
     */
    public function get_latest_hash_version(): int
    {
        return self::HASH_VERSION_ARGON2ID13_AGNOSTIC;
    }
    /**
     * Check whether specified cipher version is supported
     *
     * Returns matched supported version or throws exception
     *
     * @param int $version
     * @return int
     * @throws \Exception
     */
    public function validate_cipher($version)
    {
        $types = [self::CIPHER_BLOWFISH, self::CIPHER_RIJNDAEL_128, self::CIPHER_RIJNDAEL_256, self::CIPHER_AEAD_CHACHA20POLY1305];
        $version = (int) $version;
        if (!in_array($version, $types, true)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception((string) new \Magento\Framework\Phrase('Not supported cipher version'));
        }
        return $version;
    }
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_hash($password, $salt = false, $version = self::HASH_VERSION_LATEST)
    {
        if ($version < 0 || $version > $this->get_latest_hash_version()) {
            $version = $this->get_latest_hash_version();
        }
        $is_argon = $version === self::HASH_VERSION_ARGON2ID13 || $version === self::HASH_VERSION_ARGON2ID13_AGNOSTIC;
        if ($salt === false) {
            //Generating a simple hash without salt.
            if ($is_argon) {
                $version = self::HASH_VERSION_SHA256;
            }
            return $this->hash($password, $version);
        }
        if ($salt === true) {
            //Generate random default length salt
            $salt = self::DEFAULT_SALT_LENGTH;
        }
        if (is_integer($salt)) {
            //Generate salt of given length.
            $salt = $this->random->get_random_string($salt);
        }
        if ($is_argon) {
            $seed_bytes = SODIUM_CRYPTO_SIGN_SEEDBYTES;
            $ops_limit = SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE;
            $mem_limit = SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE;
            if ($version === self::HASH_VERSION_ARGON2ID13_AGNOSTIC) {
                $version = implode('_', [self::HASH_VERSION_ARGON2ID13_AGNOSTIC, $seed_bytes, $ops_limit, $mem_limit]);
            }
            $hash = $this->get_argon_hash($password, $seed_bytes, $ops_limit, $mem_limit, $salt);
        } else {
            $hash = $this->generate_simple_hash($salt . $password, (int) $version);
        }
        return implode(self::DELIMITER, [$hash, $salt, $version]);
    }
    /**
     * Generate simple hash for given string.
     *
     * @param string $data
     * @param int $version
     * @return string
     */
    private function generate_simple_hash(string $data, int $version): string
    {
        if (!array_key_exists($version, $this->hash_version_map)) {
            throw new \InvalidArgumentException('Unknown hashing algorithm');
        }
        return hash($this->hash_version_map[$version], (string) $data);
    }
    /**
     * @inheritdoc
     */
    public function hash($data, $version = self::HASH_VERSION_SHA256)
    {
        if (empty($this->keys[$this->key_version])) {
            throw new \RuntimeException('No key available');
        }
        if (!array_key_exists($version, $this->hash_version_map)) {
            throw new \InvalidArgumentException('Unknown hashing algorithm');
        }
        return hash_hmac($this->hash_version_map[$version], (string) $data, $this->decode_key($this->keys[$this->key_version]), false);
    }
    /**
     * @inheritdoc
     */
    public function validate_hash($password, $hash)
    {
        return $this->is_valid_hash($password, $hash);
    }
    /**
     * @inheritdoc
     */
    public function is_valid_hash($password, $hash)
    {
        $agnostic_argon_reg_ex = '/^' . self::HASH_VERSION_ARGON2ID13_AGNOSTIC . '\_(?<seed>\d+)\_(?<ops>\d+)\_(?<mem>\d+)$/';
        try {
            [$hash, $hash_salt, $hash_versions] = $this->explode_password_hash($hash);
            $recreated = $password;
            //Upgraded hashes would have been hashed with multiple algorithms.
            //Hashing the test string with every algorithm the original string has been hashed with.
            foreach ($hash_versions as $hash_version) {
                if (is_string($hash_version) && preg_match($agnostic_argon_reg_ex, $hash_version, $argon_params)) {
                    $recreated = $this->get_argon_hash($recreated, (int) $argon_params['seed'], (int) $argon_params['ops'], (int) $argon_params['mem'], $hash_salt);
                } elseif ((int) $hash_version === self::HASH_VERSION_ARGON2ID13) {
                    $recreated = $this->get_argon_hash($recreated, SODIUM_CRYPTO_SIGN_SEEDBYTES, SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE, $hash_salt);
                } else {
                    $recreated = $this->generate_simple_hash($hash_salt . $recreated, (int) $hash_version);
                }
            }
        } catch (\Throwable $exception) {
            //Hash is not a password hash.
            $recreated = $this->hash($password);
        }
        return Security::compare_strings($recreated, $hash);
    }
    /**
     * @inheritdoc
     */
    public function validate_hash_version($hash, $validate_count = false)
    {
        try {
            $hash_versions = $this->explode_password_hash($hash)[2];
        } catch (\RuntimeException $exception) {
            //Not a password hash.
            return true;
        }
        if ($this->get_latest_hash_version() === self::HASH_VERSION_ARGON2ID13_AGNOSTIC) {
            //Agnostic Argon also stores Argon parameters.
            $valid_version = preg_match('/^' . self::HASH_VERSION_ARGON2ID13_AGNOSTIC . '\_\d+\_\d+\_\d+$/', end($hash_versions));
        } else {
            $valid_version = end($hash_versions) === $this->get_latest_hash_version();
        }
        return $valid_version && (!$validate_count || count($hash_versions) === 1);
    }
    /**
     * Explode password hash
     *
     * @param string $hash
     * @return array
     * @throws \RuntimeException When given hash cannot be processed.
     */
    private function explode_password_hash($hash)
    {
        $exploded_password = $hash !== null ? explode(self::DELIMITER, $hash, 3) : [];
        if (count($exploded_password) !== 3) {
            throw new \RuntimeException('Hash is not a password hash');
        }
        //Hashes that have been upgraded will have algorithm version history starting from the oldest one used.
        $exploded_password[self::PASSWORD_VERSION] = explode(self::DELIMITER, $exploded_password[self::PASSWORD_VERSION] ?? '');
        return $exploded_password;
    }
    /**
     * Prepend key and cipher versions to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        $crypt = new Sodium_Chacha_Ietf($this->decode_key($this->keys[$this->key_version]));
        return $this->key_version . ':' . self::CIPHER_AEAD_CHACHA20POLY1305 . ':' . base64_encode($crypt->encrypt($data));
    }
    /**
     * Encrypt data using the fastest available algorithm
     *
     * @param string $data
     * @return string
     */
    public function encrypt_with_fastest_available_algorithm($data)
    {
        $crypt = $this->get_crypt();
        if (null === $crypt) {
            return $data;
        }
        return $this->key_version . ':' . $this->get_cipher_version() . ':' . base64_encode($crypt->encrypt($data));
    }
    /**
     * Look for key and crypt versions in encrypted data before decrypting
     *
     * Unsupported/unspecified key version silently fallback to the oldest we have
     * Unsupported cipher versions eventually throw exception
     * Unspecified cipher version fallback to the oldest we support
     *
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function decrypt($data)
    {
        if ($data) {
            $parts = explode(':', $data, 4);
            $parts_count = count($parts);
            $init_vector = null;
            // specified key, specified crypt, specified iv
            if (4 === $parts_count) {
                list($key_version, $crypt_version, $iv, $data) = $parts;
                $init_vector = $iv ? $iv : null;
                $key_version = (int) $key_version;
                $crypt_version = self::CIPHER_RIJNDAEL_256;
                // specified key, specified crypt
            } elseif (3 === $parts_count) {
                list($key_version, $crypt_version, $data) = $parts;
                $key_version = (int) $key_version;
                $crypt_version = (int) $crypt_version;
                // no key version = oldest key, specified crypt
            } elseif (2 === $parts_count) {
                list($crypt_version, $data) = $parts;
                $key_version = 0;
                $crypt_version = (int) $crypt_version;
                // no key version = oldest key, no crypt version = oldest crypt
            } elseif (1 === $parts_count) {
                $key_version = 0;
                $crypt_version = self::CIPHER_BLOWFISH;
                // not supported format
            } else {
                return '';
            }
            // no key for decryption
            if (!isset($this->keys[$key_version])) {
                return '';
            }
            $crypt = $this->get_crypt($this->decode_key($this->keys[$key_version]), $crypt_version, $init_vector);
            if (null === $crypt) {
                return '';
            }
            return trim($crypt->decrypt(base64_decode((string) $data)));
        }
        return '';
    }
    /**
     * Validate key contains only allowed characters
     *
     * @param string|null $key NULL value means usage of the default key specified on constructor
     * @throws \Exception
     */
    public function validate_key($key)
    {
        // @phpstan-ignore-next-line
        if (!$this->key_validator->is_valid($key)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception((string) new \Magento\Framework\Phrase('Encryption key must be 32 character string without any white space.'));
        }
    }
    /**
     * Attempt to append new key & version
     *
     * @param string $key
     * @return $this
     * @throws \Exception
     */
    public function set_new_key($key)
    {
        $this->validate_key($key);
        $this->keys[] = $key;
        $this->key_version += 1;
        return $this;
    }
    /**
     * Export current keys as string
     *
     * @return string
     */
    public function export_keys()
    {
        return implode("\n", $this->keys);
    }
    /**
     * Initialize crypt module if needed
     *
     * By default initializes with latest key and crypt versions
     *
     * @param string $key
     * @param int $cipherVersion
     * @param string $initVector
     * @return EncryptionAdapterInterface|null
     * @throws \Exception
     */
    private function get_crypt(?string $key = null, ?int $cipher_version = null, ?string $init_vector = null): ?Encryption_Adapter_Interface
    {
        //phpcs:disable PHPCompatibility.Constants.RemovedConstants
        if (null === $key && null === $cipher_version) {
            $cipher_version = $this->get_cipher_version();
        }
        if (null === $key) {
            $key = $this->decode_key($this->keys[$this->key_version]);
        }
        if (!$key) {
            return null;
        }
        if (null === $cipher_version) {
            $cipher_version = $this->cipher;
        }
        $cipher_version = $this->validate_cipher($cipher_version);
        if ($cipher_version >= self::CIPHER_AEAD_CHACHA20POLY1305) {
            return new Sodium_Chacha_Ietf($key);
        }
        if ($cipher_version === self::CIPHER_RIJNDAEL_128) {
            $cipher = MCRYPT_RIJNDAEL_128;
            $mode = MCRYPT_MODE_ECB;
        } elseif ($cipher_version === self::CIPHER_RIJNDAEL_256) {
            $cipher = MCRYPT_RIJNDAEL_256;
            $mode = MCRYPT_MODE_CBC;
        } else {
            $cipher = MCRYPT_BLOWFISH;
            $mode = MCRYPT_MODE_ECB;
        }
        //phpcs:enable PHPCompatibility.Constants.RemovedConstants
        return new Mcrypt($key, $cipher, $mode, $init_vector);
    }
    /**
     * Get cipher version
     *
     * @return int
     */
    private function get_cipher_version()
    {
        return $this->cipher;
    }
    /**
     * Generate Argon2ID13 hash.
     *
     * @param string $data
     * @param int $seedBytes
     * @param int $opsLimit
     * @param int $memLimit
     * @param string $salt
     * @return string
     * @throws \SodiumException
     */
    private function get_argon_hash(string $data, int $seed_bytes, int $ops_limit, int $mem_limit, string $salt): string
    {
        if (strlen($salt) < SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            $salt = str_pad($salt, SODIUM_CRYPTO_PWHASH_SALTBYTES, $salt);
        } elseif (strlen($salt) > SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            $salt = substr($salt, 0, SODIUM_CRYPTO_PWHASH_SALTBYTES);
        }
        return bin2hex(sodium_crypto_pwhash($seed_bytes, $data, $salt, $ops_limit, $mem_limit, SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13));
    }
    /**
     * Find out actual decode key
     *
     * @param string $key
     * @return false|string
     */
    private function decode_key(string $key): string|bool
    {
        return str_starts_with($key, Config_Options_List_Constants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX) ? base64_decode(substr($key, strlen(Config_Options_List_Constants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX))) : $key;
    }
}