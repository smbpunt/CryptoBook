<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var'])
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
        // Managed by Flex recipes: avoid diffs on every recipes:update
        'public/index.php',
        'tests/bootstrap.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP8x4Migration' => true,
        '@PHP8x4Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
