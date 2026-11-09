<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var'])
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP84Migration' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
