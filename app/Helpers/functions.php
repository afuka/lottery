<?php

if (! function_exists('json_encode_zw')) {
    /**
     * 数组对象转json时候中中文 不转码，原文输出
     *
     * @param  array  $array
     * @return string
     */
    function json_encode_zw($array)
    {
        array_recursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
    function array_recursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_recursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key]; unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}