<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read_Factory;
/**
 * This class calculates if document root is set to pub
 */
class Doc_Root_Locator
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @deprecated 102.0.2
     * @var ReadFactory
     */
    private $read_factory;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @param RequestInterface $request
     * @param ReadFactory $readFactory
     * @param Filesystem|null $filesystem
     */
    public function __construct(Request_Interface $request, Read_Factory $read_factory, ?Filesystem $filesystem = null)
    {
        $this->request = $request;
        $this->read_factory = $read_factory;
        $this->filesystem = $filesystem ?: Object_Manager::get_instance()->get(Filesystem::class);
    }
    /**
     * Returns true if doc root is pub/ and not BP
     *
     * @return bool
     */
    public function is_pub()
    {
        $root_base_path = $this->request->get_server('DOCUMENT_ROOT') ?? '';
        $read_directory = $this->filesystem->get_directory_read(Directory_List::ROOT);
        return substr($root_base_path, -\strlen('/pub')) === '/pub' && !$read_directory->is_exist('setup');
    }
}