<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Exception\File_System_Exception;
/**
 * Origin filesystem driver. Allows interacting with http endpoint like with FileSystem
 */
class Http extends File
{
    /**
     * Scheme distinguisher
     *
     * @var string
     */
    protected $scheme = 'http';
    /**
     * Checks if path exists
     *
     * @param string $path
     * @return bool
     */
    public function is_exists($path)
    {
        $headers = array_change_key_case(get_headers($this->get_scheme() . $path, 1), CASE_LOWER);
        $status = $headers[0] ?? '';
        /* Handling 301 or 302 redirection */
        if (isset($headers[1]) && preg_match('/30[12]/', $status)) {
            $status = $headers[1];
        }
        return !(strpos($status, '200') === false);
    }
    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function stat($path)
    {
        $headers = array_change_key_case(get_headers($this->get_scheme() . $path, 1), CASE_LOWER);
        $result = ['dev' => 0, 'ino' => 0, 'mode' => 0, 'nlink' => 0, 'uid' => 0, 'gid' => 0, 'rdev' => 0, 'atime' => 0, 'ctime' => 0, 'blksize' => 0, 'blocks' => 0, 'size' => isset($headers['content-length']) ? $headers['content-length'] : 0, 'type' => isset($headers['content-type']) ? $headers['content-type'] : '', 'mtime' => isset($headers['last-modified']) ? $headers['last-modified'] : 0, 'disposition' => isset($headers['content-disposition']) ? $headers['content-disposition'] : null];
        return $result;
    }
    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flags
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     */
    public function file_get_contents($path, $flags = null, $context = null)
    {
        $full_path = $this->get_scheme() . $path;
        clearstatcache(false, $full_path);
        $result = @file_get_contents($full_path, $flags ?? false, $context);
        if (false === $result) {
            throw new File_System_Exception(new \Magento\Framework\Phrase('The contents from the "%1" file can\'t be read. %2', [$path, $this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @param resource|null $context
     * @return int The number of bytes that were written
     * @throws FileSystemException
     */
    public function file_put_contents($path, $content, $mode = null, $context = null)
    {
        $result = @file_put_contents($this->get_scheme() . $path, $content, $mode, $context);
        if ($result === false) {
            throw new File_System_Exception(new \Magento\Framework\Phrase('The specified "%1" file couldn\'t be written. %2', [$path, $this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource file
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function file_open($path, $mode)
    {
        $url_prop = $this->parse_url($this->get_scheme() . $path);
        if (false === $url_prop) {
            throw new File_System_Exception(new \Magento\Framework\Phrase('The download URL is incorrect. Verify and try again.'));
        }
        $hostname = $url_prop['host'];
        $port = 80;
        if (isset($url_prop['port'])) {
            $port = (int) $url_prop['port'];
        }
        $path = '/';
        if (isset($url_prop['path'])) {
            $path = $url_prop['path'];
        }
        $query = '';
        if (isset($url_prop['query'])) {
            $query = '?' . $url_prop['query'];
        }
        $result = $this->open($hostname, $port);
        $headers = 'GET ' . $path . $query . ' HTTP/1.0' . "\r\n" . 'Host: ' . $hostname . "\r\n" . 'User-Agent: Magento' . "\r\n" . 'Connection: close' . "\r\n" . "\r\n";
        fwrite($result, $headers);
        // trim headers
        while (!feof($result)) {
            $str = fgets($result, 1024);
            if ($str == "\r\n") {
                break;
            }
        }
        return $result;
    }
    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FileSystemException
     */
    public function file_read_line($resource, $length, $ending = null)
    {
        try {
            $result = @stream_get_line($resource, $length, $ending);
        } catch (\Exception $e) {
            throw new File_System_Exception(new \Magento\Framework\Phrase('Stream get line failed %1', [$e->get_message()]));
        }
        return $result;
    }
    /**
     * Get absolute path
     *
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_absolute_path($base_path, $path, $scheme = null)
    {
        // check if the path given is already an absolute path containing the
        // basepath. so if the basepath starts at position 0 in the path, we
        // must not concatinate them again because path is already absolute.
        if (0 === strpos((string) $path, (string) $base_path)) {
            return $this->get_scheme() . $path;
        }
        return $this->get_scheme() . $base_path . $path;
    }
    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function get_scheme($scheme = null)
    {
        $scheme = $scheme ?: $this->scheme;
        return $scheme ? $scheme . '://' : '';
    }
    /**
     * Open a url
     *
     * @param string $hostname
     * @param int $port
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return resource|bool
     */
    protected function open($hostname, $port)
    {
        $result = @fsockopen($hostname, $port, $error_number, $error_message);
        if ($result === false) {
            throw new File_System_Exception(new \Magento\Framework\Phrase('Something went wrong while connecting to the host. Error#%1 - %2.', [$error_number, $error_message]));
        }
        return $result;
    }
    /**
     * Parse a http url
     *
     * @param string $path
     * @return array
     */
    protected function parse_url($path)
    {
        return parse_url($path);
    }
}