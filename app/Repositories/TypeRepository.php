<?php
namespace MineStats\Repositories;

class TypeRepository
{
    private static $types = ['PC', 'PE'];

    public static function getTypes()
    {
        return self::$types;
    }
}