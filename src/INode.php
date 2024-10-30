<?php

namespace Mifumi323\SearchQueryStructure;

interface INode
{
    /**
     * ノードの配列表現に変換します。
     * Mifumi323\SearchPhraseParser と同じ形式となります。
     */
    public function toArray(): array;
}
