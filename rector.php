<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
    ])
    ->withSkip([
        __DIR__.'/config/bundles.php',
        __DIR__.'/config/reference.php',
    ])
    ->withPhpSets()
    ->withPreparedSets(symfonyCodeQuality: true)
    ->withComposerBased(twig: true, doctrine: true, symfony: true)
    ->withAttributesSets(all: true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withCodeQualityLevel(0);
