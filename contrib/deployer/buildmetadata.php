<?php

namespace Deployer;

set('buildmetadata_file', 'build-metadata.json');

task('buildmetadata:deploy', function (): void {
    upload('{{buildmetadata_file}}', '{{release_path}}');
})->desc('Deploy build metadata');

task('buildmetadata:clear', artisan('buildmetadata:clear', ['showOutput']))
->desc('Forget cached build metadata');
