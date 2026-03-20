<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Composer\IO\Buffer_Io;
/**
 * Class creates BufferIO instance
 */
class Buffer_Io_Factory
{
    /**
     * Creates BufferIO instance
     *
     * @return BufferIO
     */
    public function create()
    {
        return new Buffer_Io();
    }
}