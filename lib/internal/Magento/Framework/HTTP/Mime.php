<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\HTTP;

/**
 * Support class for MultiPart Mime Messages
 */
class Mime
{
    public const TYPE_OCTETSTREAM         = 'application/octet-stream';
    public const TYPE_TEXT                = 'text/plain';
    public const TYPE_HTML                = 'text/html';
    public const ENCODING_7BIT            = '7bit';
    public const ENCODING_8BIT            = '8bit';
    public const ENCODING_QUOTEDPRINTABLE = 'quoted-printable';
    public const ENCODING_BASE64          = 'base64';
    public const DISPOSITION_ATTACHMENT   = 'attachment';
    public const DISPOSITION_INLINE       = 'inline';
    public const LINELENGTH               = 72;
    public const LINEEND                  = "\n";
    public const MULTIPART_ALTERNATIVE    = 'multipart/alternative';
    public const MULTIPART_MIXED          = 'multipart/mixed';
    public const MULTIPART_RELATED        = 'multipart/related';
}
