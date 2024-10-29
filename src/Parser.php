<?php

namespace Mifumi323\SearchQueryStructure;

class Parser
{
    public function __construct(public bool $ignore_case = false)
    {
    }

    /**
     * 解析します。
     */
    public function parse(string $query): INode
    {
        $symbols = $this->lexicalAnalyze($query);
        throw new \Exception('Not implemented.');
    }

    /**
     * 字句解析
     */
    private function lexicalAnalyze(string $query): array
    {
        $lex_pattern = [
            [
                'symbol' => 'AND',
                'pattern' => 'AND\b|&',
            ],
            [
                'symbol' => 'OR',
                'pattern' => 'OR\b|\|',
            ],
            [
                'symbol' => 'NOT',
                'pattern' => 'NOT\b|-',
            ],
            [
                'symbol' => 'BRST',
                'pattern' => '\(',
            ],
            [
                'symbol' => 'BREN',
                'pattern' => '\)',
            ],
            [
                'symbol' => 'PHRASE',
                'pattern' => '"(([^"]*("")?)*)"',
                'exec' => $this->quotedPhrase(...),
            ],
            [
                'symbol' => 'PHRASE',
                'pattern' => '[^\s\(\)]+',
            ],
            [
                'symbol' => '',
                'pattern' => '\s+',
            ],
        ];
        $lex = [];
        $modifier = $this->ignore_case ? 'iu' : 'u';
        do {
            $matched = false;
            foreach ($lex_pattern as $val) {
                $pattern = '/^(?:'.$val['pattern'].')/'.$modifier;
                if (preg_match($pattern, $query, $matches)) {
                    if ($val['symbol'] !== '') {
                        $exec = $val['exec'] ?? false;
                        $lex[] = [
                            'symbol' => $val['symbol'],
                            'value' => is_callable($exec) ? $exec($matches) : $matches[0],
                        ];
                    }
                    $query = mb_substr($query, mb_strlen($matches[0]));
                    $matched = true;
                    break;
                }
            }
        } while ($matched);

        return $lex;
    }

    /**
     * クォーテーションで囲まれた文字列の中身をちょっといじる
     */
    private function quotedPhrase(array $matches): string
    {
        return str_replace('""', '"', $matches[1]);
    }
}
