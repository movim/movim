<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'assets',
        'cache',
        'etc',
        'locales',
        'theme',
        'users',
        'vendor'
    ])
    ->in(__DIR__);
;
return PhpCsFixer\Config::create()
    ->setRules([
        'psr0' => false,
        '@PSR2' => true,
    ])
    ->setFinder($finder)
;