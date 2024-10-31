<?php

namespace Mifumi323\SearchQueryStructure;

class NullNode implements INode
{
    public function __construct() {
    }

    public function toArray(): array
    {
        return [];
    }
}
