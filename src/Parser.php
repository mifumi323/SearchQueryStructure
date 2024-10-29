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
        $tree = $this->EXP0($symbols, 0);
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

    // EXP0 = EXP1 ( EXP1 )*
    private function EXP0($symbols, $next)
    {
        $values = [];
        while (is_array($exp1 = $this->EXP1($symbols, $next))) {
            $values[] = $exp1;
            $next = $exp1['next'];
        }
        if (count($values) === 0) {
            return null;
        }

        return [
            'symbol' => 'EXP0',
            'type' => 'AND',
            'next' => $next,
            'value' => $values,
        ];
    }

    // EXP1 = EXP2 ( OR EXP2 )*
    private function EXP1($symbols, $next)
    {
        $values = [];
        while (is_array($exp2 = $this->EXP2($symbols, $next))) {
            $values[] = $exp2;
            $next = $exp2['next'];
            if (!isset($symbols[$next]) || $symbols[$next]['symbol'] !== 'OR') {
                break;
            }
            $next++;
        }
        if ($next > 0 && $symbols[$next - 1]['symbol'] === 'OR') {
            $next--;
        }
        if (count($values) === 0) {
            return null;
        }

        return [
            'symbol' => 'EXP1',
            'type' => 'OR',
            'next' => $next,
            'value' => $values,
        ];
    }

    // EXP2 = EXP3 ( AND EXP3 )*
    private function EXP2($symbols, $next)
    {
        $values = [];
        while (is_array($exp3 = $this->EXP3($symbols, $next))) {
            $values[] = $exp3;
            $next = $exp3['next'];
            if (!isset($symbols[$next]) || $symbols[$next]['symbol'] !== 'AND') {
                break;
            }
            $next++;
        }
        if ($next > 0 && $symbols[$next - 1]['symbol'] === 'AND') {
            $next--;
        }
        if (count($values) === 0) {
            return null;
        }

        return [
            'symbol' => 'EXP2',
            'type' => 'AND',
            'next' => $next,
            'value' => $values,
        ];
    }

    // EXP3 = ( NOT )? EXP4
    private function EXP3($symbols, $next)
    {
        $values = [];
        if (isset($symbols[$next]) && $symbols[$next]['symbol'] === 'NOT') {
            $next++;
            $exp4 = $this->EXP4($symbols, $next);
            if (!is_array($exp4)) {
                return null;
            }
            $values[] = $exp4;
            $next = $exp4['next'];

            return [
                'symbol' => 'EXP3',
                'type' => 'NOT',
                'next' => $next,
                'value' => $values,
            ];
        } else {
            $exp4 = $this->EXP4($symbols, $next);
            if (!is_array($exp4)) {
                return null;
            }
            $values[] = $exp4;
            $next = $exp4['next'];

            return [
                'symbol' => 'EXP3',
                'type' => 'EXP',
                'next' => $next,
                'value' => $values,
            ];
        }
    }

    // EXP4 = PHRASE | BRST EXP0 BREN
    private function EXP4($symbols, $next)
    {
        $values = [];
        if (isset($symbols[$next]) && $symbols[$next]['symbol'] === 'PHRASE') {
            $values[] = $symbols[$next];
            $next++;

            return [
                'symbol' => 'EXP4',
                'type' => 'TERM',
                'next' => $next,
                'value' => $values,
            ];
        } elseif (isset($symbols[$next]) && $symbols[$next]['symbol'] === 'BRST') {
            $next++;
            $exp0 = $this->EXP0($symbols, $next);
            if (!is_array($exp0)) {
                return null;
            }
            $values[] = $exp0;
            $next = $exp0['next'];
            if (!isset($symbols[$next]) || $symbols[$next]['symbol'] !== 'BREN') {
                return null;
            }
            $next++;

            return [
                'symbol' => 'EXP4',
                'type' => 'EXP',
                'next' => $next,
                'value' => $values,
            ];
        } else {
            return null;
        }
    }
}
