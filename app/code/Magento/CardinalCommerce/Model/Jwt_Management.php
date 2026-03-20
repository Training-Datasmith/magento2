<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model;

use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * JSON Web Token management.
 */
class Jwt_Management
{
    /**
     * The signing algorithm. Cardinal supported algorithm is 'HS256'
     */
    private const SIGN_ALGORITHM = 'HS256';
    /**
     * @var Json
     */
    private $json;
    /**
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }
    /**
     * Converts JWT string into array.
     *
     * @param string $jwt The JWT
     * @param string $key The secret key
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $jwt, string $key): array
    {
        if (empty($jwt)) {
            throw new \InvalidArgumentException('JWT is empty');
        }
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new \InvalidArgumentException('Wrong number of segments in JWT');
        }
        [$head_b64, $payload_b64, $signature_b64] = $parts;
        $header_json = $this->url_safe_b64decode($head_b64);
        $header = $this->json->unserialize($header_json);
        $payload_json = $this->url_safe_b64decode($payload_b64);
        $payload = $this->json->unserialize($payload_json);
        $signature = $this->url_safe_b64decode($signature_b64);
        if (!Security::compare_strings($signature, $this->sign($head_b64 . '.' . $payload_b64, $key, $header['alg']))) {
            throw new \InvalidArgumentException('JWT signature verification failed');
        }
        return $payload;
    }
    /**
     * Converts and signs array into a JWT string.
     *
     * @param array $payload
     * @param string $key
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function encode(array $payload, string $key): string
    {
        $header = ['typ' => 'JWT', 'alg' => self::SIGN_ALGORITHM];
        $header_json = $this->json->serialize($header);
        $segments[] = $this->url_safe_b64encode($header_json);
        $payload_json = $this->json->serialize($payload);
        $segments[] = $this->url_safe_b64encode($payload_json);
        $signature = $this->sign(implode('.', $segments), $key, $header['alg']);
        $segments[] = $this->url_safe_b64encode($signature);
        return implode('.', $segments);
    }
    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg The message to sign.
     * @param string $key The secret key.
     * @param string $algorithm The signing algorithm.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function sign(string $msg, string $key, string $algorithm): string
    {
        if ($algorithm !== self::SIGN_ALGORITHM) {
            throw new \InvalidArgumentException('Algorithm ' . $algorithm . ' is not supported');
        }
        return hash_hmac('sha256', $msg, $key, true);
    }
    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string
     */
    private function url_safe_b64decode(string $input): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
    }
    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string
     */
    private function url_safe_b64encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}