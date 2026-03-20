<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Utility;

/**
 * Factory for \RegexIterator
 */
class Regex_Iterator_Factory
{
    /**
     * Create instance of \RegexIterator
     *
     * @param string $directoryPath
     * @param string $regexp
     * @return \RegexIterator
     */
    public function create($directory_path, $regexp)
    {
        $directory = new \Recursive_Directory_Iterator($directory_path);
        $recursive_iterator = new \Recursive_Iterator_Iterator($directory);
        return new \Regex_Iterator($recursive_iterator, $regexp, \Regex_Iterator::GET_MATCH);
    }
}