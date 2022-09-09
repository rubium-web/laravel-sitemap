<?php

namespace Rubium\LaravelSitemap;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LaravelSitemapServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../views', 'sitemap');

        $this->registerPublishing();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sitemap.php',
            'sitemap'
        );

        $this->app->bind('sitemap', function (Container $app) {
            $config = $app->make('config');

            return new LaravelSitemap(
                $config->get('sitemap'),
                $app->make('cache.store'),
                $config,
                $app->make('files'),
                $app->make(ResponseFactory::class),
                $app->make('view')
            );
        });

        $this->app->alias('sitemap', LaravelSitemap::class);
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['sitemap', LaravelSitemap::class];
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sitemap.php' => config_path('sitemap.php'),
            ], 'sitemap-config');

            $this->publishes([
                __DIR__.'/../views' => base_path('resources/views/vendor/sitemap'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/sitemap'),
            ], ['sitemap-assets', 'laravel-assets']);
        }
    }
}
