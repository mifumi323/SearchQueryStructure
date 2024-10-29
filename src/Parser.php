<?php

namespace Mifumi323\SearchQueryStructure;

class Parser
{
    public function __construct(public bool $ignore_case = false)
    {
    }

    public function parse(string $query) : INode {
        throw new \Exception('Not implemented.');
    }
}
