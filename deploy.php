<?php

namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', getenv('DEPLOY_REPOSITORY'));

// set() replaces the recipe lists: var/cache and var/share stay per release
// (a shared cache would be overwritten by the new release while the old one is serving).
set('shared_files', ['.env.local']);
set('shared_dirs', ['var/log', 'var/sessions']);
set('writable_dirs', ['var']);
set('http_user', 'deployer');

// Hosts

host(getenv('DEPLOY_HOST'))
    ->set('remote_user', getenv('DEPLOY_USER'))
    ->set('deploy_path', getenv('DEPLOY_PATH'))
    ->set('keep_releases', getenv('DEPLOY_KEEP_RELEASES'))
    ->setPort(getenv('DEPLOY_PORT'));

// Tasks

// importmap:install already runs in the composer auto-scripts of deploy:vendors.
// tailwind:build must run before asset-map:compile, or the compiled CSS is empty.
desc('Builds the CSS and compiles the assets');
task('app:assets', static function () {
    run('{{bin/console}} tailwind:build --minify');
    run('{{bin/console}} asset-map:compile');
});

// The running worker uses the previous release path, hence its cache namespace:
// the restart signal must be sent from there. systemd restarts it in `current`.
desc('Stops the Messenger workers of the previous release');
task('app:stop-workers', static function () {
    if (!has('previous_release')) {
        return;
    }
    run('{{bin/php}} {{previous_release}}/bin/console messenger:stop-workers --no-interaction');
});

desc('Reloads FrankenPHP');
task('frankenphp:reload', static function () {
    run('sudo systemctl reload frankenphp');
});

// Hooks

after('deploy:vendors', 'app:assets');
after('app:assets', 'database:migrate');
after('deploy:symlink', 'frankenphp:reload');
after('frankenphp:reload', 'app:stop-workers');
after('deploy:failed', 'deploy:unlock');
