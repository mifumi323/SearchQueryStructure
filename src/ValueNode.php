<?php

namespace Mifumi323\SearchQueryStructure;

class ValueNode implements INode
{
    public function __construct(public string $value) {
    }
}
