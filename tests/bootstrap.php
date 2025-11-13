<?php

$paths = [
    __DIR__.'/../../vendor/autoload.php',
    __DIR__.'/../vendor/autoload.php',
];

$autoloadPath = null;

foreach ($paths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if ($autoloadPath === null) {
    fwrite(STDERR, "Unable to locate vendor/autoload.php.\n");
    exit(1);
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require $autoloadPath;

$loader->addPsr4('Rawnoq\\Settings\\Tests\\', __DIR__.'/');
