<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit74361d6bdd00907e9c735c58c6efaf6f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PhpMqtt\\Client\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'PhpMqtt\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-mqtt/client/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit74361d6bdd00907e9c735c58c6efaf6f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit74361d6bdd00907e9c735c58c6efaf6f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit74361d6bdd00907e9c735c58c6efaf6f::$classMap;

        }, null, ClassLoader::class);
    }
}