<?php

namespace Mifumi323\SearchQueryStructure;

class NotNode implements INode
{
    public function __construct(public INode $child) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'NOT',
            'value' => [$this->child->toArray()],
        ];
    }
}
