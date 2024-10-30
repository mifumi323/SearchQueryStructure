<?php

namespace Mifumi323\SearchQueryStructure;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new Parser(true);
        $actual = $parser->parse('aaa NOT bbb OR (ccc "ddd eee")');
        $expected = [
            'type' => 'AND',
            'value' => [
                [
                    'type' => 'VALUE',
                    'value' => 'aaa',
                ],
                [
                    'type' => 'OR',
                    'value' => [
                        [
                            'type' => 'NOT',
                            'value' => [
                                [
                                    'type' => 'VALUE',
                                    'value' => 'bbb',
                                ],
                            ],
                        ],
                        [
                            'type' => 'AND',
                            'value' => [
                                [
                                    'type' => 'VALUE',
                                    'value' => 'ccc',
                                ],
                                [
                                    'type' => 'VALUE',
                                    'value' => 'ddd eee',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $actual->toArray());
    }

    public function testParseOpenBranket()
    {
        $parser = new Parser(true);
        $this->expectExceptionMessage('Invalid tree');
        $actual = $parser->parse('(');
        // 途中までのパース結果を返さず、例外を出す。
        // $expected = [];
        // $this->assertSame($expected, $actual->toArray());
    }

    public function testParseCloseBranket()
    {
        $parser = new Parser(true);
        $this->expectExceptionMessage('Invalid tree');
        $actual = $parser->parse(')');
        // 途中までのパース結果を返さず、例外を出す。
        // $expected = [];
        // $this->assertSame($expected, $actual->toArray());
    }

    public function testParseInvalidBranket()
    {
        $parser = new Parser(true);
        // これをエラーとしないのはよくない気がする。
        $actual = $parser->parse('fff )(');
        $expected = [
            'type' => 'VALUE',
            'value' => 'fff',
        ];
        $this->assertSame($expected, $actual->toArray());
    }

    public function testParseQuotedQuote()
    {
        $parser = new Parser(true);
        $actual = $parser->parse('"aaa""bbb"');
        $expected = [
            'type' => 'VALUE',
            'value' => 'aaa"bbb',
        ];
        $this->assertSame($expected, $actual->toArray());
    }

    public function testParseEmpty()
    {
        $parser = new Parser(true);
        $this->expectExceptionMessage('Invalid tree');
        $actual = $parser->parse('');
        // 途中までのパース結果を返さず、例外を出す。
        // $expected = [];
        // $this->assertSame($expected, $actual->toArray());
    }

    public function testParseOrWithoutLeftValue()
    {
        // 実際に来たSQLインジェクション試行のリクエスト
        // これを防ぐのは当ライブラリの責任範囲ではないため、普通にパースを試みる(初手ORなのでどのみち失敗に終わる)
        $parser = new Parser(true);
        $this->expectExceptionMessage('Invalid tree');
        $actual = $parser->parse(' or (1,2)=(select*from(select name_const(CHAR(108,79,100,111,120,85,77,85,114,116),1),name_const(CHAR(108,79,100,111,120,85,77,85,114,116),1))a) -- and 1=1');
        // 途中までのパース結果を返さず、例外を出す。
        // $expected = [];
        // $this->assertSame($expected, $actual->toArray());
    }

    public function testParseContinuousAndOr()
    {
        $parser = new Parser(true);
        // これをエラーとしないのはよくない気がする。
        $actual = $parser->parse('a and or b');
        $expected = [
            'type' => 'VALUE',
            'value' => 'a',
        ];
        $this->assertSame($expected, $actual->toArray());
    }

    public function testParseOpenBranketAndCharacter()
    {
        $parser = new Parser(true);
        // これをエラーとしないのはよくない気がする。
        $actual = $parser->parse('t0b@5d@(y ');
        $expected = [
            'type' => 'VALUE',
            'value' => 't0b@5d@',
        ];
        $this->assertSame($expected, $actual->toArray());
    }
}
