<?php

namespace App\Shared\Helpers;

class PrinterCustom
{
    public static function print($data): void
    {
        if(self::isDebug()) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
    }

    private static function isDebug(): bool
    {
        return  ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    }

}
