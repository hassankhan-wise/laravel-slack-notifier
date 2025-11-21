<?php

namespace HKWise\LaravelSlackNotifier;

use Illuminate\Support\ServiceProvider;

class SlackNotifierServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Merge the package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config/slack-notifier.php',
            'slack-notifier'
        );

        // Bind the SlackNotifier class to the service container
        $this->app->singleton('slack-notifier', function ($app) {
            return new SlackNotifier();
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__ . '/config/slack-notifier.php' => config_path('slack-notifier.php'),
        ], 'slack-notifier-config');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\SlackTestCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['slack-notifier'];
    }
}
