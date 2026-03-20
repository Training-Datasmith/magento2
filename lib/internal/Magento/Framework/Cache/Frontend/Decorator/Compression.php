<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\Frontend_Interface;
/**
 * Compression decorator for Symfony cache frontend
 *
 * Compresses cache data before storing to reduce memory usage and network bandwidth.
 * Compatible with legacy Zend cache compression format for seamless migration.
 */
class Compression extends Bare
{
    /**
     * Compression prefix to identify compressed data
     */
    private const COMPRESSION_PREFIX = 'CACHE_COMPRESSION';
    /**
     * @var int
     */
    private int $threshold;
    /**
     * @var string
     */
    private string $compression_lib;
    /**
     * @var int
     */
    private int $compression_level;
    /**
     * Constructor
     *
     * @param FrontendInterface $frontend
     * @param int $threshold Minimum data size in bytes to trigger compression (default: 2048)
     * @param string $compressionLib Compression library: gzip, snappy, lzf, lz4, zstd (default: gzip)
     * @param int $compressionLevel Compression level 1-9, higher = better compression but slower (default: 6)
     */
    public function __construct(Frontend_Interface $frontend, int $threshold = 2048, string $compression_lib = 'gzip', int $compression_level = 6)
    {
        parent::__construct($frontend);
        $this->threshold = max(1, $threshold);
        $this->compression_lib = strtolower($compression_lib);
        $this->compression_level = max(1, min(9, $compression_level));
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null): bool
    {
        // Only compress string data that exceeds threshold
        if (is_string($data) && strlen($data) > $this->threshold) {
            $compressed = $this->compress_data($data);
            // Only use compressed version if it's actually smaller
            if ($compressed !== false && strlen($compressed) < strlen($data)) {
                $data = self::COMPRESSION_PREFIX . $compressed;
            }
        }
        return parent::save($data, $identifier, $tags, $life_time);
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $data = parent::load($identifier);
        // Decompress if data has compression prefix
        if (is_string($data) && $this->is_compressed($data)) {
            $decompressed = $this->decompress_data($data);
            if ($decompressed !== false) {
                return $decompressed;
            }
        }
        return $data;
    }
    /**
     * Compress data using configured compression library
     *
     * @param string $data
     * @return string|false Compressed data or false on failure
     */
    private function compress_data(string $data)
    {
        try {
            return match ($this->compression_lib) {
                'snappy' => $this->compress_snappy($data),
                'lzf' => $this->compress_lzf($data),
                'lz4' => $this->compress_lz4($data),
                'zstd' => $this->compress_zstd($data),
                'gzip', '' => $this->compress_gzip($data),
                default => $this->compress_gzip($data),
            };
        } catch (\Throwable $e) {
            // Silently fallback to uncompressed on any compression error
            return false;
        }
    }
    /**
     * Decompress data by auto-detecting compression method
     *
     * @param string $data
     * @return string|false Decompressed data or false on failure
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function decompress_data(string $data)
    {
        // Remove compression prefix
        $data = substr($data, strlen(self::COMPRESSION_PREFIX));
        try {
            // Auto-detect compression method by trying each in order
            // Try gzip first (most common)
            if (($result = $this->decompress_gzip($data)) !== false) {
                return $result;
            }
            // Try other formats
            if (extension_loaded('snappy') && ($result = $this->decompress_snappy($data)) !== false) {
                return $result;
            }
            if (extension_loaded('lzf') && ($result = $this->decompress_lzf($data)) !== false) {
                return $result;
            }
            if (extension_loaded('lz4') && ($result = $this->decompress_lz4($data)) !== false) {
                return $result;
            }
            if (extension_loaded('zstd') && ($result = $this->decompress_zstd($data)) !== false) {
                return $result;
            }
            return false;
        } catch (\Throwable $e) {
            // Return false on any decompression error
            return false;
        }
    }
    /**
     * Check if data is compressed
     *
     * @param string $data
     * @return bool
     */
    private function is_compressed(string $data): bool
    {
        return str_starts_with($data, self::COMPRESSION_PREFIX);
    }
    /**
     * Compress using gzip
     *
     * @param string $data
     * @return string|false
     */
    private function compress_gzip(string $data)
    {
        return gzcompress($data, $this->compression_level);
    }
    /**
     * Decompress using gzip
     *
     * @param string $data
     * @return string|false
     */
    private function decompress_gzip(string $data)
    {
        return @gzuncompress($data);
    }
    /**
     * Compress using Snappy
     *
     * @param string $data
     * @return string|false
     */
    private function compress_snappy(string $data)
    {
        if (!extension_loaded('snappy')) {
            return false;
        }
        return snappy_compress($data);
    }
    /**
     * Decompress using Snappy
     *
     * @param string $data
     * @return string|false
     */
    private function decompress_snappy(string $data)
    {
        if (!extension_loaded('snappy')) {
            return false;
        }
        return @snappy_uncompress($data);
    }
    /**
     * Compress using LZF
     *
     * @param string $data
     * @return string|false
     */
    private function compress_lzf(string $data)
    {
        if (!extension_loaded('lzf')) {
            return false;
        }
        return lzf_compress($data);
    }
    /**
     * Decompress using LZF
     *
     * @param string $data
     * @return string|false
     */
    private function decompress_lzf(string $data)
    {
        if (!extension_loaded('lzf')) {
            return false;
        }
        return @lzf_decompress($data);
    }
    /**
     * Compress using LZ4
     *
     * @param string $data
     * @return string|false
     */
    private function compress_lz4(string $data)
    {
        if (!extension_loaded('lz4')) {
            return false;
        }
        return lz4_compress($data, $this->compression_level);
        // @phpstan-ignore-line
    }
    /**
     * Decompress using LZ4
     *
     * @param string $data
     * @return string|false
     */
    private function decompress_lz4(string $data)
    {
        if (!extension_loaded('lz4')) {
            return false;
        }
        return @lz4_uncompress($data);
        // @phpstan-ignore-line
    }
    /**
     * Compress using Zstd
     *
     * @param string $data
     * @return string|false
     */
    private function compress_zstd(string $data)
    {
        if (!extension_loaded('zstd')) {
            return false;
        }
        return zstd_compress($data, $this->compression_level);
    }
    /**
     * Decompress using Zstd
     *
     * @param string $data
     * @return string|false
     */
    private function decompress_zstd(string $data)
    {
        if (!extension_loaded('zstd')) {
            return false;
        }
        return @zstd_uncompress($data);
    }
}