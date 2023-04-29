<?php

namespace App\Utilities;

final class Config
{
    public static function getJwtKeyMaterial()
    {
        return $_ENV['JWT_KEY_MATERIAL'] ?? 'WmKhJ#Uv^3GP';
    }

    public static function getJwtAlgorithm()
    {
        return $_ENV['JWT_ALGORITHM'] ?? 'HS256';
    }
}