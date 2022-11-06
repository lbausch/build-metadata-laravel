# Build Metadata for Laravel <!-- omit in toc -->

![tests](https://github.com/lbausch/build-metadata-laravel/actions/workflows/tests.yml/badge.svg) ![codecov](https://codecov.io/gh/lbausch/build-metadata-laravel/branch/main/graph/badge.svg)

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
  - [Configuration](#configuration)
  - [Saving Build Metadata](#saving-build-metadata)
  - [Deployer Recipe](#deployer-recipe)
  - [Using Build Metadata](#using-build-metadata)
  - [Callbacks](#callbacks)
    - [beforeCaching](#beforecaching)

## Requirements
+ PHP 8.1+
+ Laravel 9+

## Installation
```bash
composer require lbausch/build-metadata-laravel
```

## Usage

### Configuration
If the default configuration doesn't suit your needs, you may publish the [configuration file](config/build-metadata.php):
```bash
php artisan vendor:publish --provider=Lbausch\\BuildMetadataLaravel\\ServiceProvider
```

### Saving Build Metadata
When deploying your application, e.g. utilizing a CI/CD pipeline, you may save build metadata with the following command:
```bash
php artisan buildmetadata:save BUILD_REF=$CI_COMMIT_SHA BUILD_DATE=$(date +%s)
```
Build metadata are indefinitely cached, so the application cache needs to be cleared during deployment.

### Deployer Recipe
This package ships with a Deployer recipe which provides a task to upload the build metadata.

```php
<?php

// deploy.php

namespace Deployer;

require 'vendor/lbausch/build-metadata-laravel/contrib/deployer/buildmetadata.php';

// ...

after('deploy:vendors', 'buildmetadata:deploy');

// ...
```

### Using Build Metadata
In the following example build metadata are retrieved within a [view composer](https://laravel.com/docs/9.x/views#view-composers).

```php
<?php

// app/Providers/ViewServiceProvider.php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Lbausch\BuildMetadataLaravel\BuildMetadataManager;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(BuildMetadataManager $manager)
    {
        $metadata = $manager->getMetadata();

        View::composer('*', function ($view) use ($metadata) {
            $view->with('BUILD_REF', $metadata->get('BUILD_REF', 'n/a'));
        });
    }
}
```


### Callbacks

#### beforeCaching

```php
<?php

// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Lbausch\BuildMetadataLaravel\BuildMetadataManager;
use Lbausch\BuildMetadataLaravel\Metadata;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register a callback which is executed before metadata are indefinitely cached
        BuildMetadataManager::beforeCaching(function (Metadata $metadata): Metadata {
            // Convert build date to a Carbon instance
            $build_date = $metadata->get('BUILD_DATE');

            $metadata->set('BUILD_DATE', Carbon::createFromTimestampUTC($build_date));

            return $metadata;
        });
    }
}
```
