<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Archive;

use Magento\Framework\Archive\Helper\File;
/**
 * Class to work with tar archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tar extends \Magento\Framework\Archive\Abstract_Archive implements \Magento\Framework\Archive\Archive_Interface
{
    /**
     * The value of the tar block size
     *
     * @const int
     */
    public const TAR_BLOCK_SIZE = 512;
    /**
     * Keep file or directory for packing.
     *
     * @var string
     */
    protected $_current_file;
    /**
     * Keep path to file or directory for packing.
     *
     * @var string
     */
    protected $_current_path;
    /**
     * Skip first level parent directory. Example:
     *   use test/fip.php instead test/test/fip.php;
     *
     * @var bool
     */
    protected $_skip_root;
    /**
     * Tarball data writer
     *
     * @var File
     */
    protected $_writer;
    /**
     * Tarball data reader
     *
     * @var File
     */
    protected $_reader;
    /**
     * Path to file where tarball should be placed
     *
     * @var string
     */
    protected $_destination_file_path;
    /**
     * Initialize tarball writer
     *
     * @return $this
     */
    protected function _init_writer()
    {
        $this->_writer = new File($this->_destination_file_path);
        $this->_writer->open('w');
        return $this;
    }
    /**
     * Returns string that is used for tar's header parsing
     *
     * @return string
     */
    protected static function _get_format_parse_header()
    {
        return 'Z100name/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Z1type/Z100symlink/Z6magic/Z2version/' . 'Z32uname/Z32gname/Z8devmajor/Z8devminor/Z155prefix/Z12closer';
    }
    /**
     * Destroy tarball writer
     *
     * @return $this
     */
    protected function _destroy_writer()
    {
        if ($this->_writer instanceof File) {
            $this->_writer->close();
            $this->_writer = null;
        }
        return $this;
    }
    /**
     * Get tarball writer
     *
     * @return File
     */
    protected function _get_writer()
    {
        if (!$this->_writer) {
            $this->_init_writer();
        }
        return $this->_writer;
    }
    /**
     * Initialize tarball reader
     *
     * @return $this
     */
    protected function _init_reader()
    {
        $this->_reader = new File($this->_get_current_file());
        $this->_reader->open('r');
        return $this;
    }
    /**
     * Destroy tarball reader
     *
     * @return $this
     */
    protected function _destroy_reader()
    {
        if ($this->_reader instanceof File) {
            $this->_reader->close();
            $this->_reader = null;
        }
        return $this;
    }
    /**
     * Get tarball reader
     *
     * @return File
     */
    protected function _get_reader()
    {
        if (!$this->_reader) {
            $this->_init_reader();
        }
        return $this->_reader;
    }
    /**
     * Set option that define ability skip first catalog level.
     *
     * @param bool $skipRoot
     * @return $this
     */
    protected function _set_skip_root($skip_root)
    {
        $this->_skip_root = $skip_root;
        return $this;
    }
    /**
     * Set file which is packing.
     *
     * @param string $file
     * @return $this
     */
    protected function _set_current_file($file)
    {
        $file = $file !== null ? str_replace('\\', '/', $file) : '';
        $this->_current_file = $file . (!is_link($file) && is_dir($file) && substr($file, -1) != '/' ? '/' : '');
        return $this;
    }
    /**
     * Set path to file where tarball should be placed
     *
     * @param string $destinationFilePath
     * @return $this
     */
    protected function _set_destination_file_path($destination_file_path)
    {
        $this->_destination_file_path = $destination_file_path;
        return $this;
    }
    /**
     * Retrieve file which is packing.
     *
     * @return string
     */
    protected function _get_current_file()
    {
        return $this->_current_file;
    }
    /**
     * Set path to file which is packing.
     *
     * @param string $path
     * @return $this
     */
    protected function _set_current_path($path)
    {
        $path = $path !== null ? str_replace('\\', '/', $path) : '';
        if ($this->_skip_root && is_dir($path)) {
            $this->_current_path = $path . (substr($path, -1) != '/' ? '/' : '');
        } else {
            $this->_current_path = dirname($path) . '/';
        }
        return $this;
    }
    /**
     * Retrieve path to file which is packing.
     *
     * @return string
     */
    protected function _get_current_path()
    {
        return $this->_current_path;
    }
    /**
     * Recursively walk through file tree and create tarball
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _create_tar($skip_root = false, $finalize = false)
    {
        if (!$skip_root) {
            $this->_pack_and_write_current_file();
        }
        $file = $this->_get_current_file();
        if (is_dir($file)) {
            $dir_files = scandir($file, SCANDIR_SORT_NONE);
            if (false === $dir_files) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Can\'t scan dir: %1', [$file]));
            }
            $dir_files = array_diff($dir_files, ['..', '.']);
            foreach ($dir_files as $item) {
                $this->_set_current_file($file . $item)->_create_tar();
            }
        }
        if ($finalize) {
            $this->_get_writer()->write(str_repeat("\x00", self::TAR_BLOCK_SIZE * 12));
        }
    }
    /**
     * Write current file to tarball
     *
     * @return void
     */
    protected function _pack_and_write_current_file()
    {
        $archive_writer = $this->_get_writer();
        $archive_writer->write($this->_compose_header());
        $current_file = $this->_get_current_file();
        $file_size = 0;
        if (is_file($current_file) && !is_link($current_file)) {
            $file_reader = new File($current_file);
            $file_reader->open('r');
            while (!$file_reader->eof()) {
                $archive_writer->write($file_reader->read());
            }
            $file_reader->close();
            $file_size = filesize($current_file);
        }
        $append_zeros_count = (self::TAR_BLOCK_SIZE - $file_size % self::TAR_BLOCK_SIZE) % self::TAR_BLOCK_SIZE;
        $archive_writer->write(str_repeat("\x00", $append_zeros_count));
    }
    /**
     * Compose header for current file in TAR format.
     *
     * If length of file's name greater 100 characters,
     * method breaks header into two pieces. First contains
     * header and data with long name. Second contain only header.
     *
     * @param bool $long
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _compose_header($long = false)
    {
        $file = $this->_get_current_file() ?? '';
        $path = $this->_get_current_path() ?? '';
        $info_file = stat($file);
        $name_file = str_replace($path, '', $file);
        $name_file = str_replace('\\', '/', $name_file);
        $packed_header = '';
        $long_header = '';
        if (!$long && strlen($name_file) > 100) {
            $long_header = $this->_compose_header(true);
            $long_header .= str_pad($name_file, floor((strlen($name_file) + 512 - 1) / 512) * 512, "\x00");
        }
        $header = [];
        $header['100-name'] = $long ? '././@LongLink' : substr($name_file, 0, 100);
        $header['8-mode'] = $long ? '       ' : str_pad(substr(sprintf('%07o', $info_file['mode']), -4), 6, '0', STR_PAD_LEFT);
        $header['8-uid'] = $long || $info_file['uid'] == 0 ? "\x00\x00\x00\x00\x00\x00\x00" : sprintf('%07o', $info_file['uid']);
        $header['8-gid'] = $long || $info_file['gid'] == 0 ? "\x00\x00\x00\x00\x00\x00\x00" : sprintf('%07o', $info_file['gid']);
        $header['12-size'] = $long ? sprintf('%011o', strlen($name_file)) : sprintf('%011o', is_dir($file) ? 0 : filesize($file));
        $header['12-mtime'] = $long ? '00000000000' : sprintf('%011o', $info_file['mtime']);
        $header['8-check'] = sprintf('% 8s', '');
        $header['1-type'] = $long ? 'L' : (is_link($file) ? 2 : (is_dir($file) ? 5 : 0));
        $header['100-symlink'] = is_link($file) ? readlink($file) : '';
        $header['6-magic'] = 'ustar ';
        $header['2-version'] = ' ';
        $a = function_exists('posix_getpwuid') && posix_getpwuid(fileowner($file)) ? posix_getpwuid(fileowner($file)) : ['name' => ''];
        $header['32-uname'] = $a['name'];
        $a = function_exists('posix_getgrgid') && posix_getpwuid(fileowner($file)) ? posix_getgrgid(filegroup($file)) : ['name' => ''];
        $header['32-gname'] = $a['name'];
        $header['8-devmajor'] = '';
        $header['8-devminor'] = '';
        $header['155-prefix'] = '';
        $header['12-closer'] = '';
        $packed_header = '';
        foreach ($header as $key => $element) {
            $length = explode('-', $key);
            $packed_header .= pack('a' . $length[0], $element);
        }
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($packed_header, $i, 1));
        }
        $packed_header = substr_replace($packed_header, sprintf('%07o', $checksum) . "\x00", 148, 8);
        return $long_header . $packed_header;
    }
    /**
     * Read TAR string from file, and unpacked it.
     *
     * Create files and directories information about described
     * in the string.
     *
     * @param string $destination path to file is unpacked
     * @return string[] list of files
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _unpack_current_tar($destination)
    {
        $archive_reader = $this->_get_reader();
        $list = [];
        while (!$archive_reader->eof()) {
            $header = $this->_extract_file_header();
            if (!$header) {
                continue;
            }
            $current_file = $destination . $header['name'];
            $dirname = dirname($current_file);
            if (in_array($header['type'], ['0', chr(0), ''])) {
                if (!file_exists($dirname)) {
                    $mkdir_result = @mkdir($dirname, 0777, true);
                    if (false === $mkdir_result) {
                        throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Failed to create directory %1', [$dirname]));
                    }
                }
                $this->_extract_and_write_file($header, $current_file);
                $list[] = $current_file;
            } elseif ($header['type'] == '5') {
                if (!file_exists($dirname)) {
                    $mkdir_result = @mkdir($current_file, $header['mode'], true);
                    if (false === $mkdir_result) {
                        throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Failed to create directory %1', [$current_file]));
                    }
                }
                $list[] = $current_file . '/';
            } elseif ($header['type'] == '2') {
                //we do not interrupt unpack process if symlink creation failed as symlinks are not so important
                @symlink($header['symlink'], $current_file);
            }
        }
        return $list;
    }
    /**
     * Read and decode file header information from tarball
     *
     * @return array|bool
     */
    protected function _extract_file_header()
    {
        $archive_reader = $this->_get_reader();
        $header_block = $archive_reader->read(self::TAR_BLOCK_SIZE);
        if (strlen($header_block) < self::TAR_BLOCK_SIZE) {
            return false;
        }
        $header = unpack(self::_get_format_parse_header(), $header_block);
        $header['mode'] = octdec($header['mode']);
        $header['uid'] = octdec($header['uid']);
        $header['gid'] = octdec($header['gid']);
        $header['size'] = octdec($header['size']);
        $header['mtime'] = octdec($header['mtime']);
        $header['checksum'] = octdec($header['checksum']);
        if ($header['type'] == '5') {
            $header['size'] = 0;
        }
        $checksum = 0;
        $header_block = substr_replace($header_block, '        ', 148, 8);
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($header_block, $i, 1));
        }
        $checksum_ok = $header['checksum'] == $checksum;
        if (isset($header['name']) && $checksum_ok) {
            $header['name'] = trim($header['name']);
            if (!($header['name'] == '././@LongLink' && $header['type'] == 'L')) {
                return $header;
            }
            $real_name_block_size = floor(($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE) * self::TAR_BLOCK_SIZE;
            $real_name_block = $archive_reader->read($real_name_block_size);
            $real_name = substr($real_name_block, 0, $header['size']);
            $header_main = $this->_extract_file_header();
            $header_main['name'] = trim($real_name);
            return $header_main;
        }
        return false;
    }
    /**
     * Extract next file from tarball by its $header information and save it to $destination
     *
     * @param array $fileHeader
     * @param string $destination
     * @return void
     */
    protected function _extract_and_write_file($file_header, $destination)
    {
        $file_writer = new File($destination);
        $file_writer->open('w', $file_header['mode']);
        $archive_reader = $this->_get_reader();
        $filesize = $file_header['size'];
        $bytes_extracted = 0;
        while ($filesize > $bytes_extracted && !$archive_reader->eof()) {
            $block = $archive_reader->read(self::TAR_BLOCK_SIZE);
            $non_extracted_bytes_count = $filesize - $bytes_extracted;
            $data = substr($block, 0, $non_extracted_bytes_count);
            $file_writer->write($data);
            $bytes_extracted += strlen($block);
        }
    }
    /**
     * Pack file to TAR (Tape Archiver).
     *
     * @param string $source
     * @param string $destination
     * @param bool $skipRoot
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function pack($source, $destination, $skip_root = false)
    {
        $this->_set_skip_root($skip_root);
        $source = realpath($source);
        $tar_data = $this->_set_current_path($source)->_set_destination_file_path($destination)->_set_current_file($source);
        $this->_init_writer();
        $this->_create_tar($skip_root, true);
        $this->_destroy_writer();
        return $destination;
    }
    /**
     * Unpack file from TAR (Tape Archiver).
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination)
    {
        $this->_set_current_file($source)->_set_current_path($source);
        $this->_init_reader();
        $this->_unpack_current_tar($destination);
        $this->_destroy_reader();
        return $destination;
    }
    /**
     * Extract one file from TAR (Tape Archiver).
     *
     * @param string $file
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function extract($file, $source, $destination)
    {
        $this->_set_current_file($source);
        $this->_init_reader();
        $archive_reader = $this->_get_reader();
        $extracted_file = '';
        while (!$archive_reader->eof()) {
            $header = $this->_extract_file_header();
            if ($header['name'] == $file) {
                $extracted_file = $destination . basename($header['name']);
                $this->_extract_and_write_file($header, $extracted_file);
                break;
            }
            if ($header['type'] != 5) {
                $skip_bytes = floor(($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE) * self::TAR_BLOCK_SIZE;
                $archive_reader->read($skip_bytes);
            }
        }
        $this->_destroy_reader();
        return $extracted_file;
    }
}