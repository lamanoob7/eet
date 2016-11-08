<?php

/**
 * Created by PhpStorm.
 * User: LamA.nOOb
 * Date: 06.11.2016
 * Time: 11:20
 */
class Lamanoob7_EET_DispatcherCert_Storage
{
    public static $data;

    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    public static function get($name)
    {
        return self::$data[$name];
    }
}