# Build Metadata for Laravel <!-- omit in toc -->

![tests](https://github.com/lbausch/build-metadata-laravel/actions/workflows/tests.yml/badge.svg) ![codecov](https://codecov.io/gh/lbausch/build-metadata-laravel/branch/main/graph/badge.svg)

Save arbitrary build metadata (commit SHA, build date, ...), deploy them along with your application and retrieve them at runtime when required.

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
  - [Configuration](#configuration)
  - [Saving Build Metadata](#saving-build-metadata)
  - [Deployer Recipe](#deployer-recipe)
  - [Using Build Metadata at Runtime](#using-build-metadata-at-runtime)
  - [Callbacks](#callbacks)
    - [beforeCaching](#beforecaching)
  - [Events](#events)
    - [CachingBuildMetadata](#cachingbuildmetadata)
    - [CachedBuildMetadata](#cachedbuildmetadata)

## Requirements
+ PHP 8.3+
+ Laravel 12+

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
When deploying your application, e.g. utilizing a CI/CD pipeline, the following command writes build metadata to the configured file:
```bash
php artisan buildmetadata:save BUILD_REF=$CI_COMMIT_SHA BUILD_DATE=$(date +%s)
```
Build metadata are indefinitely cached, so either the application cache needs to be cleared during deployment or the following command may be used:
```bash
php artisan buildmetadata:clear
```

### Deployer Recipe
This package ships with a Deployer recipe which provides tasks to handle the build metadata.

```php
<?php

// deploy.php

namespace Deployer;

require 'vendor/lbausch/build-metadata-laravel/contrib/deployer/buildmetadata.php';

// ...

after('deploy:vendors', 'buildmetadata:deploy');

after('artisan:config:cache', 'buildmetadata:clear');

// ...
```

### Using Build Metadata at Runtime
In the following example build metadata are retrieved within a [view composer](https://laravel.com/docs/master/views#view-composers).

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
This callback is executed before metadata are indefinitely cached and might be used to alter some of the data.

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
        BuildMetadataManager::beforeCaching(function (Metadata $metadata): Metadata {
            // Convert build date to a Carbon instance
            $build_date = $metadata->get('BUILD_DATE');

            $metadata->set('BUILD_DATE', Carbon::createFromTimestampUTC($build_date));

            return $metadata;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
```

### Events

#### CachingBuildMetadata
This event is dispatched right before build metadata will be cached.

```php
<?php

// app/Providers/EventServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(function(\Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata $event) {
            //
        });
    }
}
```

#### CachedBuildMetadata
This event is dispatched after build metadata were cached. The cached build metadata are available on the event instance.

```php
<?php

// app/Providers/EventServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(function(\Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata $event) {
            $build_metadata = $event->build_metadata;
        });
    }
}
