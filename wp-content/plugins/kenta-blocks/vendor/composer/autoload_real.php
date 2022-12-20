<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitd7ca60bf99a0bc2c48b8e2a0603d3c1b
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitd7ca60bf99a0bc2c48b8e2a0603d3c1b', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitd7ca60bf99a0bc2c48b8e2a0603d3c1b', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitd7ca60bf99a0bc2c48b8e2a0603d3c1b::getInitializer($loader));

        $loader->register(true);

        $includeFiles = \Composer\Autoload\ComposerStaticInitd7ca60bf99a0bc2c48b8e2a0603d3c1b::$files;
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequired7ca60bf99a0bc2c48b8e2a0603d3c1b($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequired7ca60bf99a0bc2c48b8e2a0603d3c1b($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}
