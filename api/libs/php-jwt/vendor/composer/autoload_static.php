<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9edfcbce33e758f8e16d6112cec2f18f
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9edfcbce33e758f8e16d6112cec2f18f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9edfcbce33e758f8e16d6112cec2f18f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}