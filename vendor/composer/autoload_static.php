<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit45c6e3c098105a6067d826c7dc443f2b
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GetOpt\\' => 7,
        ),
        'E' => 
        array (
            'EBookLib\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GetOpt\\' => 
        array (
            0 => __DIR__ . '/..' . '/ulrichsg/getopt-php/src',
        ),
        'EBookLib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'EBookLib\\BrowserDisplay' => __DIR__ . '/../..' . '/classes/BrowserDisplay.php',
        'EBookLib\\CommandLine' => __DIR__ . '/../..' . '/classes/CommandLine.php',
        'EBookLib\\Dispatcher' => __DIR__ . '/../..' . '/classes/Dispatcher.php',
        'EBookLib\\Ebook' => __DIR__ . '/../..' . '/classes/Ebook.php',
        'EBookLib\\Library' => __DIR__ . '/../..' . '/classes/Library.php',
        'EBookLib\\MetaBook' => __DIR__ . '/../..' . '/classes/MetaBook.php',
        'EBookLib\\Template' => __DIR__ . '/../..' . '/classes/Template.php',
        'GetOpt\\Argument' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Argument.php',
        'GetOpt\\ArgumentException' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/ArgumentException.php',
        'GetOpt\\ArgumentException\\Invalid' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/ArgumentException/Invalid.php',
        'GetOpt\\ArgumentException\\Missing' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/ArgumentException/Missing.php',
        'GetOpt\\ArgumentException\\Unexpected' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/ArgumentException/Unexpected.php',
        'GetOpt\\Arguments' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Arguments.php',
        'GetOpt\\Command' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Command.php',
        'GetOpt\\Describable' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Describable.php',
        'GetOpt\\GetOpt' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/GetOpt.php',
        'GetOpt\\Help' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Help.php',
        'GetOpt\\HelpInterface' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/HelpInterface.php',
        'GetOpt\\Operand' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Operand.php',
        'GetOpt\\Option' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Option.php',
        'GetOpt\\OptionParser' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/OptionParser.php',
        'GetOpt\\Translator' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/Translator.php',
        'GetOpt\\WithMagicGetter' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/WithMagicGetter.php',
        'GetOpt\\WithOperands' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/WithOperands.php',
        'GetOpt\\WithOptions' => __DIR__ . '/..' . '/ulrichsg/getopt-php/src/WithOptions.php',
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
