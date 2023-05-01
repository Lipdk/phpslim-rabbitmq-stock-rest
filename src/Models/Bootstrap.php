<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;

final class Bootstrap
{
    public static function load($container)
    {
        $settings = $container->get('settings');
        $capsule = new Capsule();
        $capsule->addConnection($settings['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}