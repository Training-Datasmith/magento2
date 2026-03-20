<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Filesystem\Iterator;

/**
 * File lines iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class File extends \Spl_File_Object
{
    /**
     * The statement that was last read during iteration
     *
     * @var string
     */
    protected $_current_statement = '';
    /**
     * Store current statement delimiter.
     *
     * @var string
     */
    private string $statement_delimiter = ';';
    /**
     * Return current sql statement
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        return $this->_current_statement;
    }
    /**
     * Iterate to next sql statement in file
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        $this->_current_statement = '';
        while (!$this->eof()) {
            $line = $this->fgets();
            $trimmed_line = trim($line);
            if (!empty($trimmed_line) && !$this->is_delimiter_changed($trimmed_line)) {
                $statement_final_line = '/(?<statement>.*)' . preg_quote($this->statement_delimiter, '/') . '$/';
                if (preg_match($statement_final_line, $trimmed_line, $matches)) {
                    $this->_current_statement .= $matches['statement'];
                    break;
                } else {
                    $this->_current_statement .= $line;
                }
            }
        }
    }
    /**
     * Check whether statement delimiter has been changed.
     *
     * @param string $line
     * @return bool
     */
    private function is_delimiter_changed(string $line): bool
    {
        if (preg_match('/^delimiter\s+(?<delimiter>.+)$/i', $line, $matches)) {
            $this->statement_delimiter = $matches['delimiter'];
            return true;
        }
        return false;
    }
    /**
     * Return to first statement
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        parent::rewind();
        $this->next();
    }
    /**
     * Check whether provided string is comment
     *
     * @param string $line
     * @return bool
     */
    protected function _is_comment($line)
    {
        return $line[0] == '#' || $line && substr($line, 0, 2) == '--';
    }
}