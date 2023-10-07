<?php

namespace bxkj_common;


class DataTemplate
{
    public static function parse($template, &$data)
    {
        if (self::isAssoArr($template) && is_array($data) && empty($data)) {
            $data = (object)$data;
        } else {
            if (is_array($data)) {
                foreach ($data as $key => &$value) {
                    if (is_int($key)) {
                        if (isset($template[$key])) {
                            self::parse($template[$key], $value);
                        } else if (isset($template[0])) {
                            self::parse($template[0], $value);
                        }
                    } else {
                        if (isset($template[$key])) {
                            self::parse($template[$key], $value);
                        }
                    }
                }
            } else {
                $data = self::varTo($template, $data);
            }
        }
    }

    public static function varTo($value1, $value2)
    {
        if (is_string($value1)) {
            return (string)$value2;
        } else if (is_int($value1)) {
            return (int)$value2;
        } else if (is_float($value1)) {
            return (float)$value2;
        } else if (is_bool($value1)) {
            return (bool)$value2;
        }
        return $value2;
    }

    public static function isAssoArr($arr)
    {
        if (is_object($arr) && get_class($arr) == 'stdClass') {
            return true;
        } else {
            if (!empty($arr) && is_array($arr)) {
                foreach ($arr as $key => $value) {
                    if (is_string($key)) return true;
                }
            }
        }
        return false;
    }

}