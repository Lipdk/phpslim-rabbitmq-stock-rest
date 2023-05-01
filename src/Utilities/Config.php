<?php
declare(strict_types=1);

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

    public static function getMailerDefaultSender()
    {
        return $_ENV['MAILER_DEFAULT_FROM'] ?? 'admin@email.com';
    }

    public static function getStockApiUrl()
    {
        return $_ENV['STOCK_API_URL'] ?? 'https://stooq.com/q/l/?s=%s&f=sd2t2ohlcv&h&e=json';
    }
}