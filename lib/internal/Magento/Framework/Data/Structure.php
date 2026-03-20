<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * An associative data structure, that features "nested set" parent-child relations
 */
class Structure implements Reset_After_Request_Interface
{
    /**
     * Reserved keys for storing structural relations
     */
    public const PARENT = 'parent';
    public const CHILDREN = 'children';
    public const GROUPS = 'groups';
    /**
     * @var array
     */
    protected $_elements = [];
    /**
     * Set elements in constructor
     *
     * @param array $elements
     */
    public function __construct(?array $elements = null)
    {
        if (null !== $elements) {
            $this->import_elements($elements);
        }
    }
    /**
     * Set elements from external source
     *
     * @param array $elements
     * @return void
     * @throws LocalizedException if any format issues identified
     */
    public function import_elements(array $elements)
    {
        $this->_elements = $elements;
        foreach ($elements as $element_id => $element) {
            if (is_numeric($element_id)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase("Element ID must not be numeric: '%1'.", [$element_id]));
            }
            $this->_assert_parent_relation($element_id);
            if (isset($element[self::GROUPS])) {
                $groups = $element[self::GROUPS];
                $this->_assert_array($groups);
                foreach ($groups as $group_name => $group) {
                    $this->_assert_array($group);
                    if ($group !== array_flip($group)) {
                        throw new Localized_Exception(new \Magento\Framework\Phrase('"%2" is an invalid format of "%1" group. Verify the format and try again.', [$group_name, var_export($group, 1)]));
                    }
                    foreach ($group as $group_element_id) {
                        $this->_assert_element_exists($group_element_id);
                    }
                }
            }
        }
    }
    /**
     * Verify relations of parent-child
     *
     * @param string $elementId
     * @return void
     * @throws LocalizedException
     */
    protected function _assert_parent_relation($element_id)
    {
        $element = $this->_elements[$element_id];
        // element presence in its parent's nested set
        if (isset($element[self::PARENT])) {
            $parent_id = $element[self::PARENT];
            $this->_assert_element_exists($parent_id);
            if (empty($this->_elements[$parent_id][self::CHILDREN][$element_id])) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" is not in the nested set of "%2", causing the parent-child relation to break. ' . 'Verify and try again.', [$element_id, $parent_id]));
            }
        }
        // element presence in its children
        if (isset($element[self::CHILDREN])) {
            $children = $element[self::CHILDREN];
            $this->_assert_array($children);
            if ($children !== array_flip(array_flip($children))) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" format of children is invalid. Verify and try again.', [var_export($children, 1)]));
            }
            foreach (array_keys($children) as $child_id) {
                $this->_assert_element_exists($child_id);
                if (!isset($this->_elements[$child_id][self::PARENT]) || $element_id !== $this->_elements[$child_id][self::PARENT]) {
                    throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" doesn\'t have "%2" as parent, causing the parent-child relation to break. ' . 'Verify and try again.', [$child_id, $element_id]));
                }
            }
        }
    }
    /**
     * Dump all elements
     *
     * @return array
     */
    public function export_elements()
    {
        return $this->_elements;
    }
    /**
     * Create new element
     *
     * @param string $elementId
     * @param array $data
     * @return void
     * @throws LocalizedException if an element with this id already exists
     */
    public function create_element($element_id, array $data)
    {
        if (isset($this->_elements[$element_id])) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('An element with a "%1" ID already exists.', [$element_id]));
        }
        $this->_elements[$element_id] = [];
        foreach ($data as $key => $value) {
            $this->set_attribute($element_id, $key, $value);
        }
    }
    /**
     * Get existing element
     *
     * @param string $elementId
     * @return array|bool
     */
    public function get_element($element_id)
    {
        return $this->_elements[$element_id] ?? false;
    }
    /**
     * Whether specified element exists
     *
     * @param string $elementId
     * @return bool
     */
    public function has_element($element_id)
    {
        return isset($this->_elements[$element_id]);
    }
    /**
     * Remove element with specified ID from the structure
     *
     * Can recursively delete all child elements.
     * Returns false if there was no element found, therefore was nothing to delete.
     *
     * @param string $elementId
     * @param bool $recursive
     * @return bool
     */
    public function unset_element($element_id, $recursive = true)
    {
        if (isset($this->_elements[$element_id][self::CHILDREN])) {
            foreach (array_keys($this->_elements[$element_id][self::CHILDREN]) as $child_id) {
                $this->_assert_element_exists($child_id);
                if ($recursive) {
                    $this->unset_element($child_id, $recursive);
                } else {
                    unset($this->_elements[$child_id][self::PARENT]);
                }
            }
        }
        $this->unset_child($element_id);
        $was_found = isset($this->_elements[$element_id]);
        unset($this->_elements[$element_id]);
        return $was_found;
    }
    /**
     * Set an arbitrary value to specified element attribute
     *
     * @param string $elementId
     * @param string $attribute
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function set_attribute($element_id, $attribute, $value)
    {
        $this->_assert_element_exists($element_id);
        switch ($attribute) {
            case self::PARENT:
            // break is intentionally omitted
            case self::CHILDREN:
            case self::GROUPS:
                throw new \InvalidArgumentException("The '{$attribute}' attribute is reserved and can't be set.");
            default:
                $this->_elements[$element_id][$attribute] = $value;
        }
        return $this;
    }
    /**
     * Get element attribute
     *
     * @param string $elementId
     * @param string $attribute
     * @return mixed
     */
    public function get_attribute($element_id, $attribute)
    {
        $this->_assert_element_exists($element_id);
        if (isset($this->_elements[$element_id][$attribute])) {
            return $this->_elements[$element_id][$attribute];
        }
        return false;
    }
    /**
     * Rename element ID
     *
     * @param string $oldId
     * @param string $newId
     * @return $this
     * @throws LocalizedException if trying to overwrite another element
     */
    public function rename_element($old_id, $new_id)
    {
        $this->_assert_element_exists($old_id);
        if (!$new_id || isset($this->_elements[$new_id])) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('An element with a "%1" ID is already defined.', [$new_id]));
        }
        // rename in registry
        $this->_elements[$new_id] = $this->_elements[$old_id];
        // rename references in children
        if (isset($this->_elements[$old_id][self::CHILDREN])) {
            foreach (array_keys($this->_elements[$old_id][self::CHILDREN]) as $child_id) {
                $this->_assert_element_exists($child_id);
                $this->_elements[$child_id][self::PARENT] = $new_id;
            }
        }
        // rename key in its parent's children array
        if (isset($this->_elements[$old_id][self::PARENT]) && $parent_id = $this->_elements[$old_id][self::PARENT]) {
            $alias = $this->_elements[$parent_id][self::CHILDREN][$old_id];
            $offset = $this->_get_child_offset($parent_id, $old_id);
            unset($this->_elements[$parent_id][self::CHILDREN][$old_id]);
            $this->set_as_child($new_id, $parent_id, $alias, $offset);
        }
        unset($this->_elements[$old_id]);
        return $this;
    }
    /**
     * Set element as a child to another element
     *
     * @param string $elementId
     * @param string $parentId
     * @param string $alias
     * @param int|null $position
     * @see _insertChild() for position explanation
     * @return void
     * @throws LocalizedException if attempting to set parent as child to its child (recursively)
     */
    public function set_as_child($element_id, $parent_id, $alias = '', $position = null)
    {
        if ($element_id == $parent_id) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" was incorrectly set as a child to itself. Resolve the issue and try again.', [$element_id]));
        }
        if ($this->_is_parent_recursively($element_id, $parent_id)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The "%3" cannot be set as child to "%1" because "%1" is a parent of "%2" recursively. ' . 'Resolve the issue and try again.', [$element_id, $parent_id, $element_id]));
        }
        $this->unset_child($element_id);
        unset($this->_elements[$parent_id][self::CHILDREN][$element_id]);
        $this->_insert_child($parent_id, $element_id, $position, $alias);
    }
    /**
     * Unset element as a child of another element
     *
     * Note that only parent-child relations will be deleted. Element itself will be retained.
     * The method is polymorphic:
     *   1 argument: element ID which is supposedly a child of some element
     *   2 arguments: parent element ID and child alias
     *
     * @param string $elementId ID of an element or its parent element
     * @param string|null $alias
     * @return $this
     */
    public function unset_child($element_id, $alias = null)
    {
        if (null === $alias) {
            $child_id = $element_id;
        } else {
            $child_id = $this->get_child_id($element_id, $alias);
        }
        $parent_id = $this->get_parent_id($child_id);
        unset($this->_elements[$child_id][self::PARENT]);
        if ($parent_id) {
            unset($this->_elements[$parent_id][self::CHILDREN][$child_id]);
            if (empty($this->_elements[$parent_id][self::CHILDREN])) {
                unset($this->_elements[$parent_id][self::CHILDREN]);
            }
        }
        return $this;
    }
    /**
     * Reorder a child element relatively to specified position
     *
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param int|null $position
     * @return int
     * @see _insertChild() for position explanation
     */
    public function reorder_child($parent_id, $child_id, $position)
    {
        $alias = $this->get_child_alias($parent_id, $child_id);
        $current_offset = $this->_get_child_offset($parent_id, $child_id);
        $offset = $position;
        if ($position > 0) {
            if ($position >= $current_offset + 1) {
                --$offset;
            }
        } elseif ($position < 0) {
            if ($position < $current_offset + 1 - count($this->_elements[$parent_id][self::CHILDREN])) {
                if ($position === -1) {
                    $offset = null;
                } else {
                    ++$offset;
                }
            }
        }
        $this->unset_child($child_id)->_insert_child($parent_id, $child_id, $offset, $alias);
        return $this->_get_child_offset($parent_id, $child_id) + 1;
    }
    /**
     * Reorder an element relatively to its sibling
     *
     * $offset possible values:
     *    1,  2 -- set after the sibling towards end -- by 1, by 2 positions, etc
     *   -1, -2 -- set before the sibling towards start -- by 1, by 2 positions, etc...
     *
     * Both $childId and $siblingId must be children of the specified $parentId
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param string $siblingId
     * @param int $offset
     * @return int
     */
    public function reorder_to_sibling($parent_id, $child_id, $sibling_id, $offset)
    {
        $this->_get_child_offset($parent_id, $child_id);
        if ($child_id === $sibling_id) {
            $new_offset = $this->_get_relative_offset($parent_id, $sibling_id, $offset);
            return $this->reorder_child($parent_id, $child_id, $new_offset);
        }
        $alias = $this->get_child_alias($parent_id, $child_id);
        $new_offset = $this->unset_child($child_id)->_get_relative_offset($parent_id, $sibling_id, $offset);
        $this->_insert_child($parent_id, $child_id, $new_offset, $alias);
        return $this->_get_child_offset($parent_id, $child_id) + 1;
    }
    /**
     * Calculate new offset for placing an element relatively specified sibling under the same parent
     *
     * @param string $parentId
     * @param string $siblingId
     * @param int $delta
     * @return int
     */
    private function _get_relative_offset($parent_id, $sibling_id, $delta)
    {
        $new_offset = $this->_get_child_offset($parent_id, $sibling_id) + $delta;
        if ($delta < 0) {
            ++$new_offset;
        }
        if ($new_offset < 0) {
            $new_offset = 0;
        }
        return $new_offset;
    }
    /**
     * Get child ID by parent ID and alias
     *
     * @param string $parentId
     * @param string $alias
     * @return string|bool
     */
    public function get_child_id($parent_id, $alias)
    {
        if ($parent_id !== null && isset($this->_elements[$parent_id][self::CHILDREN])) {
            return array_search($alias, $this->_elements[$parent_id][self::CHILDREN]);
        }
        return false;
    }
    /**
     * Get all children
     *
     * Returns in format 'id' => 'alias'
     *
     * @param string $parentId
     * @return array
     */
    public function get_children($parent_id)
    {
        return $parent_id !== null && isset($this->_elements[$parent_id][self::CHILDREN]) ? $this->_elements[$parent_id][self::CHILDREN] : [];
    }
    /**
     * Get name of parent element
     *
     * @param string $childId
     * @return string|bool
     */
    public function get_parent_id($child_id)
    {
        $child_id = $child_id ?? '';
        return $this->_elements[$child_id][self::PARENT] ?? false;
    }
    /**
     * Get element alias by name
     *
     * @param string $parentId
     * @param string $childId
     * @return string|bool
     */
    public function get_child_alias($parent_id, $child_id)
    {
        if (isset($this->_elements[$parent_id][self::CHILDREN][$child_id])) {
            return $this->_elements[$parent_id][self::CHILDREN][$child_id];
        }
        return false;
    }
    /**
     * Add element to parent group
     *
     * @param string $childId
     * @param string $groupName
     * @return bool
     */
    public function add_to_parent_group($child_id, $group_name)
    {
        $parent_id = $this->get_parent_id($child_id);
        if ($parent_id) {
            $this->_assert_element_exists($parent_id);
            $this->_elements[$parent_id][self::GROUPS][$group_name][$child_id] = $child_id;
            return true;
        }
        return false;
    }
    /**
     * Get element IDs for specified group
     *
     * Note that it is expected behavior if a child has been moved out from this parent,
     * but still remained in the group of old parent. The method will return only actual children.
     * This is intentional, in case if the child returns back to the old parent.
     *
     * @param string $parentId Name of an element containing group
     * @param string $groupName
     * @return array
     */
    public function get_group_child_names($parent_id, $group_name)
    {
        $result = [];
        if (isset($this->_elements[$parent_id][self::GROUPS][$group_name])) {
            foreach ($this->_elements[$parent_id][self::GROUPS][$group_name] as $child_id) {
                if (isset($this->_elements[$parent_id][self::CHILDREN][$child_id])) {
                    $result[] = $child_id;
                }
            }
        }
        return $result;
    }
    /**
     * Calculate a relative offset of a child element in specified parent
     *
     * @param string $parentId
     * @param string $childId
     * @return int
     * @throws LocalizedException if specified elements have no parent-child relation
     */
    protected function _get_child_offset($parent_id, $child_id)
    {
        $index = array_search($child_id, array_keys($this->get_children($parent_id)));
        if (false === $index) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The "%1" is not a child of "%2". Resolve the issue and try again.', [$child_id, $parent_id]));
        }
        return $index;
    }
    /**
     * Traverse through hierarchy and detect if the "potential parent" is a parent recursively to specified "child"
     *
     * @param string $childId
     * @param string $potentialParentId
     * @return bool
     */
    private function _is_parent_recursively($child_id, $potential_parent_id)
    {
        $parent_id = $this->get_parent_id($potential_parent_id);
        if (!$parent_id) {
            return false;
        }
        if ($parent_id === $child_id) {
            return true;
        }
        return $this->_is_parent_recursively($child_id, $parent_id);
    }
    /**
     * Insert an existing element as a child to existing element
     *
     * The element must not be a child to any other element
     * The target parent element must not have it as a child already
     *
     * Offset -- into which position to insert:
     *   0     -- set as 1st
     *   1,  2 -- after 1st, second, etc...
     *  -1, -2 -- before last, before second last, etc...
     *   null  -- set as last
     *
     * @param string $targetParentId
     * @param string $elementId
     * @param int|null $offset
     * @param string $alias
     * @return void
     * @throws LocalizedException
     */
    protected function _insert_child($target_parent_id, $element_id, $offset, $alias)
    {
        $alias = $alias ?: $element_id;
        // validate
        $this->_assert_element_exists($element_id);
        if (!empty($this->_elements[$element_id][self::PARENT])) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The element "%1" can\'t have a parent because "%2" is already the parent of "%1".', [$element_id, $this->_elements[$element_id][self::PARENT]]));
        }
        $this->_assert_element_exists($target_parent_id);
        $children = $this->get_children($target_parent_id);
        if (isset($children[$element_id])) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The element "%1" is already a child of "%2".', [$element_id, $target_parent_id]));
        }
        if (false !== array_search($alias, $children)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The element "%1" can\'t have a child because "%1" already has a child with alias "%2".', [$target_parent_id, $alias]));
        }
        // insert
        if (null === $offset) {
            $offset = count($children);
        }
        $this->_elements[$target_parent_id][self::CHILDREN] = array_merge(array_slice($children, 0, $offset), [$element_id => $alias], array_slice($children, $offset));
        $this->_elements[$element_id][self::PARENT] = $target_parent_id;
    }
    /**
     * Check if specified element exists
     *
     * @param string $elementId
     * @return void
     * @throws LocalizedException if doesn't exist
     */
    private function _assert_element_exists($element_id)
    {
        if (!isset($this->_elements[$element_id])) {
            throw new \OutOfBoundsException('The element with the "' . $element_id . '" ID wasn\'t found. Verify the ID and try again.');
        }
    }
    /**
     * Check if it is an array
     *
     * @param array $value
     * @return void
     * @throws LocalizedException
     */
    private function _assert_array($value)
    {
        if (!is_array($value)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('An array expected: %1', [var_export($value, 1)]));
        }
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->_elements = [];
    }
}