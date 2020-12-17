<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb506456900befa9e0562235568ad09f6
{
    public static $files = array (
        'da253f61703e9c22a5a34f228526f05a' => __DIR__ . '/..' . '/wixel/gump/gump.class.php',
    );

    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GUMP\\' => 5,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GUMP\\' => 
        array (
            0 => __DIR__ . '/..' . '/wixel/gump/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Slim' => 
            array (
                0 => __DIR__ . '/..' . '/slim/slim',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb506456900befa9e0562235568ad09f6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb506456900befa9e0562235568ad09f6::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb506456900befa9e0562235568ad09f6::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}