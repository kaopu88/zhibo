<?php

namespace bxkj_common;

class Pluralize
{
    public static function convert($string)
    {
        $plural = array(
            array('/(quiz)$/i', "$1zes"),
            array('/^(ox)$/i', "$1en"),
            array('/([m|l])ouse$/i', "$1ice"),
            array('/(matr|vert|ind)ix|ex$/i', "$1ices"),
            array('/(x|ch|ss|sh)$/i', "$1es"),
            array('/([^aeiouy]|qu)y$/i', "$1ies"),
            array('/([^aeiouy]|qu)ies$/i', "$1y"),
            array('/(hive)$/i', "$1s"),
            array('/(?:([^f])fe|([lr])f)$/i', "$1$2ves"),
            array('/sis$/i', "ses"),
            array('/([ti])um$/i', "$1a"),
            array('/(buffal|tomat)o$/i', "$1oes"),
            array('/(bu)s$/i', "$1ses"),
            array('/(alias|status)$/i', "$1es"),
            array('/(octop|vir)us$/i', "$1i"),
            array('/(ax|test)is$/i', "$1es"),
            array('/s$/i', "s"),
            array('/$/', "s")
        );

        $singular = array(
            array("/s$/", ""),
            array("/(n)ews$/", "$1ews"),
            array("/([ti])a$/", "$1um"),
            array("/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/", "$1$2sis"),
            array("/(^analy)ses$/", "$1sis"),
            array("/([^f])ves$/", "$1fe"),
            array("/(hive)s$/", "$1"),
            array("/(tive)s$/", "$1"),
            array("/([lr])ves$/", "$1f"),
            array("/([^aeiouy]|qu)ies$/", "$1y"),
            array("/(s)eries$/", "$1eries"),
            array("/(m)ovies$/", "$1ovie"),
            array("/(x|ch|ss|sh)es$/", "$1"),
            array("/([m|l])ice$/", "$1ouse"),
            array("/(bus)es$/", "$1"),
            array("/(o)es$/", "$1"),
            array("/(shoe)s$/", "$1"),
            array("/(cris|ax|test)es$/", "$1is"),
            array("/([octop|vir])i$/", "$1us"),
            array("/(alias|status)es$/", "$1"),
            array("/^(ox)en/", "$1"),
            array("/(vert|ind)ices$/", "$1ex"),
            array("/(matr)ices$/", "$1ix"),
            array("/(quiz)zes$/", "$1")
        );


        $irregular = array(
            array('move', 'moves'),
            array('sex', 'sexes'),
            array('child', 'children'),
            array('man', 'men'),
            array('person', 'people')
        );

        $uncountable = array(
            'sheep',
            'fish',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment'
        );

        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $uncountable))
            return $string;

        // check for irregular singular forms
        foreach ($irregular as $noun) {
            if (strtolower($string) == $noun[0])
                return $noun[1];
        }

        // check for matches using regular expressions
        foreach ($plural as $pattern) {
            if (preg_match($pattern[0], $string))
                return preg_replace($pattern[0], $pattern[1], $string);
        }

        return $string;
    }
}