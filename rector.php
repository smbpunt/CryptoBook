<?php

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/config/bundles.php',
        __DIR__.'/config/reference.php',
        // Managed by the phpunit/phpunit recipe: avoid diffs on every recipes:update
        __DIR__.'/tests/bootstrap.php',
        // Keep Doctrine mapping on plain properties: promotion can widen types used to infer relations
        ClassPropertyAssignToConstructorPromotionRector::class => [__DIR__.'/src/Entity', __DIR__.'/src/Wallet/Entity'],
    ])
    ->withPhpSets()
    ->withPreparedSets(symfonyCodeQuality: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withAttributesSets(all: true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withCodeQualityLevel(0);
