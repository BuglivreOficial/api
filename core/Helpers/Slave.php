<?php

namespace Core\Helpers;


class Slave
{
    public static function inProduction()
    {
        return $_SERVER['SERVER_ADDR'] == '127.0.0.1';
    }
}