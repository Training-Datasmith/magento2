<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Uses other extractors
 */
class Composite_Request_Type_Extractor implements Request_Type_Extractor_Interface
{
    /**
     * @var RequestTypeExtractorInterface[]
     */
    private array $extractors;
    /**
     * @param RequestTypeExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }
    /**
     * @inheritDoc
     */
    public function extract(Request_Interface $request, Action_Interface $action): ?string
    {
        foreach ($this->extractors as $extractor) {
            $type = $extractor->extract($request, $action);
            if ($type) {
                return $type;
            }
        }
        return null;
    }
}