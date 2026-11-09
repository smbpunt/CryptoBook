<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
    ])
    ->withSkip([
        __DIR__.'/config/bundles.php',
    ])
    ->withSymfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withPhpSets()
    ->withComposerBased(twig: true, doctrine: true, symfony: true)
    ->withAttributesSets()
;
