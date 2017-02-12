<?php
namespace artygrand;

class Shortcode
{
    private $codes;
    private static $instance;

    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function add($name, $value)
    {
        $this->codes[$name] = $value;

        return self::$instance;
    }

    public function withAlias($alias)
    {
        end($this->codes);
        $last = key($this->codes);
        $this->codes[$alias] = $this->codes[$last];
        return self::$instance;
    }

    public function remove($name)
    {
        unset($this->codes[$name]);

        return self::$instance;
    }

    public function compile($text)
    {
        foreach ($this->codes as $code => $func) {
            $defaults = [];
            if (is_array($func)) {
                $defaults = $func[1];
                $func = $func[0];
            }

            $open = '['.$code;
            $close = '[/'.$code.']';
            $data = [];
            $matches = $this->matchBraces($text, $open, $close);

            foreach ($matches as $start => $len) {
                if ($len > 0) {
                    $find = substr($text, $start, $len);
                    $attr_close = strpos($find, ']');
                    $content = substr($text, $start + 1 + $attr_close, $len - strlen($close) - 1 - $attr_close);
                } else {
                    $attr_close = strpos($text, ']', $start) - $start;
                    $find = substr($text, $start, 1 + $attr_close);
                    $content = null;
                }

                $attr_str = substr($find, strlen($open), $attr_close - strlen($open));
                $attr = array_merge($defaults, $this->parseAttr($attr_str));
                $replace = call_user_func_array($func, [$attr, $content]);

                $data[] = [
                    $find,
                    $replace,
                ];
            }

            foreach ($data as $d) {
                $text = $this->replace($d[0], $d[1], $text);
            }
            $text = str_replace('@'.$open, $open, $text);
        }

        return $text;
    }

    private function parseAttr($attr_string)
    {
        $attr = [];
        $pattern = '/([\w-]+)\s*(=\s*"([^"]*)")?/';
        preg_match_all($pattern, $attr_string, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $name = strtolower($match[1]);
            $value = isset($match[3]) ? $match[3] : true;
            $attr[$name] = $value;
        }

        return $attr;
    }

    private function replace($search, $replace, $text)
    {
        $pos = strpos($text, $search);

        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }

    private function matchBraces($str, $open_tag = '{{', $close_tag = '}}')
    {
        $stack = $result = [];
        $len_open = strlen($open_tag);
        $len_close = strlen($close_tag);
        $pos = -1;
        $end = strlen($str) + 1;

        while (true) {
            $p1 = strpos($str, $open_tag, $pos + 1);
            $p2 = strpos($str, $close_tag, $pos + 1);
            $pos = min(($p1 === false) ? $end : $p1, ($p2 === false) ? $end : $p2);
            if ($pos == $end) {
                break;
            }
            if (substr($str, $pos, $len_open) == $open_tag) {
                $stack[] = $pos;
            } elseif (substr($str, $pos, $len_close) == $close_tag) {
                if (count($stack)) {
                    $start = array_pop($stack);
                    if ($str{$start - 1} != '@') {
                        $result[$start] = $pos + $len_close - $start;
                    }
                }
            }
        };

        // self-closing shortcodes
        foreach ($stack as $start) {
            if ($str{$start - 1} != '@') {
                $result[$start] = 0;
            }
        }

        ksort($result);

        return $result;
    }
}
