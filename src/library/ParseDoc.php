<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littler\library;

class ParseDoc
{
    private $params;

    public function parse($doc = '')
    {
        if ($doc == '') {
            return $this->params;
        }
        // Get the comment
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return $this->params;
        }
        $comment = trim($comment[1]);
        // Get all the lines and strip the * from the first character
        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) {
            return $this->params;
        }
        $this->parseLines($lines[1]);
        return $this->params;
    }

    private function parseLines($lines)
    {
        $desc = [];
        foreach ($lines as $line) {
            $parsedLine = $this->parseLine($line); // Parse the line
            if ($parsedLine === false && ! isset($this->params['description'])) {
                if (isset($desc)) {
                    // Store the first line in the short description
                    $this->params['description'] = implode(PHP_EOL, $desc);
                }
                $desc = [];
            } elseif ($parsedLine !== false) {
                $desc[] = $parsedLine; // Store the line in the long description
            }
        }
        $desc = implode(' ', $desc);
        if (! empty($desc)) {
            $this->params['description'] = $desc;
        }
    }

    private function parseLine($line)
    {
        // trim the whitespace from the line
        $line = trim($line);
        if (empty($line)) {
            return false;
        } // Empty line

        if (strpos($line, '@') === 0) {
            $pos = 0;
            if ($pos = strpos($line, 'Group')) {
                $param = substr($line, $pos, $pos + 4);
                $value = str_replace(['(', ')', '"'], '', substr($line, $pos + 5));
            } elseif ($pos = strpos($line, 'Route')) {
                $param = substr($line, $pos, $pos + 4);
                $value = explode(',', str_replace(['(', ')', ' ', '"'], '', substr($line, $pos + 5)));
            } elseif ($pos = strpos($line, 'Resource')) {
                $param = substr($line, $pos, $pos + 7);
                $value = str_replace(['(', ')', '"'], '', substr($line, $pos + 8));
            } elseif ($pos = strpos($line, 'apiRoute')) {
                $param = substr($line, $pos, $pos + 4);
                $value = str_replace(['(', ')', '"'], '', substr($line, $pos + 7));
            } elseif (strpos($line, ' ') > 0) {
                $param = substr($line, 1, strpos($line, ' ') - 1);
                $value = substr($line, strlen($param) + 2);
            } else {
                $param = substr($line, 1);
                $value = '';
            }
            // dd($line);

            // dd($param . '=>' . $value);

            // Parse the line and return false if the parameter is valid
            if ($this->setParam($param, $value)) {
                return false;
            }
        }
        // dd($line);
        return $line;
    }

    private function setParam($param, $value)
    {
        $param = strtolower(trim($param));
        if ($param == 'param' || $param == 'return') {
            $value = $this->formatParamOrReturn($value);
        }
        if ($param == 'class') {
            [$param, $value] = $this->formatClass($value);
        }

        if (empty($this->params[$param])) {
            $this->params[$param] = $value;
        } elseif ($param == 'param') {
            if (is_array($this->params[$param])) {
                $this->params[$param] = array_unique(array_merge($this->params[$param], $value));
            } else {
                $this->params[$param] = $value;
            }
        } else {
            $this->params[$param] = $value;
        }
        return true;
    }

    private function formatClass($value)
    {
        $r = preg_split('[\\(|\\)]', $value);
        if (is_array($r)) {
            $param = $r[0];
            parse_str($r[1], $value);
            foreach ($value as $key => $val) {
                $val = explode(',', $val);
                if (count($val) > 1) {
                    $value[$key] = $val;
                }
            }
        } else {
            $param = 'Unknown';
        }
        return [
            $param,
            $value,
        ];
    }

    private function formatParamOrReturn($string)
    {
        $pos = strpos($string, ' ');

        if ($pos > 0) {
            $type = substr($string, 0, $pos);
            return ['(' . $type . ')' . substr($string, $pos + 1)];
        }
        return [substr($string, $pos + 1)];
    }
}
