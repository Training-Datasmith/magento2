<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource;

class Tree_Builder
{
    /**
     * Transform resource list into sorted resource tree that includes only active resources
     *
     * @param array $resourceList
     * @return array
     */
    public function build(array $resource_list)
    {
        $result = [];
        foreach ($resource_list as $resource) {
            if ($resource['disabled']) {
                continue;
            }
            unset($resource['disabled']);
            $resource['children'] = $this->build($resource['children']);
            $result[] = $resource;
        }
        usort($result, [$this, '_sortTree']);
        return $result;
    }
    /**
     * Sort ACL resource nodes
     *
     * @param array $nodeA
     * @param array $nodeB
     * @return int
     */
    protected function _sort_tree(array $node_a, array $node_b)
    {
        return $node_a['sortOrder'] < $node_b['sortOrder'] ? -1 : ($node_a['sortOrder'] > $node_b['sortOrder'] ? 1 : 0);
    }
}