<?php

namespace Mifumi323\SearchQueryStructure;

class OrNode implements INode
{
    /**
     * @param INode[] $children
     */
    public function __construct(public array $children) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'OR',
            'value' => array_map(fn($child) => $child->toArray(), $this->children),
        ];
    }
}
