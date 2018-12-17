<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit45c6e3c098105a6067d826c7dc443f2b
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'EBookLib\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'EBookLib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'EBookLib\\BrowserDisplay' => __DIR__ . '/../..' . '/classes/BrowserDisplay.php',
        'EBookLib\\Dispatcher' => __DIR__ . '/../..' . '/classes/Dispatcher.php',
        'EBookLib\\Ebook' => __DIR__ . '/../..' . '/classes/Ebook.php',
        'EBookLib\\Library' => __DIR__ . '/../..' . '/classes/Library.php',
        'EBookLib\\MetaBook' => __DIR__ . '/../..' . '/classes/MetaBook.php',
        'EBookLib\\Template' => __DIR__ . '/../..' . '/classes/Template.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit45c6e3c098105a6067d826c7dc443f2b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit45c6e3c098105a6067d826c7dc443f2b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit45c6e3c098105a6067d826c7dc443f2b::$classMap;

        }, null, ClassLoader::class);
    }
}
