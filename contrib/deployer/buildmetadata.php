<?php

namespace Deployer;

set('buildmetadata_file', 'build-metadata.json');

task('buildmetadata:deploy', function () {
    upload('{{buildmetadata_file}}', '{{release_path}}');
})->desc('Deploy build metadata');

task('buildmetadata:clear', function () {
    artisan('buildmetadata:clear');
})->desc('Forget cached build metadata');
