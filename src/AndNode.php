<?php

namespace Mifumi323\SearchQueryStructure;

class AndNode implements INode
{
    /**
     * @param INode[] $children
     */
    public function __construct(public array $children) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'AND',
            'value' => array_map(fn($child) => $child->toArray(), $this->children),
        ];
    }
}
