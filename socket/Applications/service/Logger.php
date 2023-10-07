<?php
/**
 * Created by PhpStorm.
 * Author: belost
 * Date: 19-5-30
 * Time: 下午8:26
 */

namespace app\service;


class Logger
{
    private static $path = ROOT_PATH.'/log';



    //写日志
    private static function write($file_name, $message)
    {
        strpos($file_name, '.log') || $file_name .= '.log';

        if (!is_file((string)$file_name))
        {
            touch($file_name);

            chmod($file_name, 0622);
        }

        file_put_contents((string)$file_name, $message.PHP_EOL, FILE_APPEND | LOCK_EX);
    }


    public static function info($message, $files='')
    {
        global $config;

        if (!$config['logger']) return;

        if (!is_dir(self::$path.'/info')) mkdir(self::$path.'/info', 0777, true);

        if ($files) $files = self::$path.'/info/'.$files;

        $message = date('Y-m-d H:i:s').' : info :  '.$message;

        self::write($files, $message);
    }


    public static function wrong($message, $files='')
    {
        global $config;

        if (!$config['logger']) return;

        if (!is_dir(self::$path.'/wrong')) mkdir(self::$path.'/wrong', 0777, true);

        if ($files) $files = self::$path.'/wrong/'.$files;

        $message = date('Y-m-d H:i:s').' : wrong :  '.$message;

        self::write($files, $message);

    }

    public static function note($message, $files='')
    {
        global $config;

        if (!$config['logger']) return;

        if (!is_dir(self::$path.'/note')) mkdir(self::$path.'/note', 0777, true);

        if ($files) $files = self::$path.'/note/'.$files;

        $message = date('Y-m-d H:i:s').' : note :  '.$message;

        self::write($files, $message);

    }


    public static function log($level, $message)
    {
        global $config;

        if (!$config['logger']) return;


    }


}