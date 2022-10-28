<?php

namespace Deployer;

set('buildmetadata_file', 'build-metadata.json');

task('buildmetadata:deploy', function () {
    upload('{{buildmetadata_file}}', '{{release_path}}');
})->desc('Deploy build metadata');
