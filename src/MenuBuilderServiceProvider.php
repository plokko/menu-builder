<?php

namespace plokko\MenuBuilder;

use Illuminate\Support\ServiceProvider;

class MenuBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //-- Publish config file --//
        /*
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('menu-builder.php'),
        ], 'config');
        /* //--- Console commands ---///
        if ($this->app->runningInConsole())
        {
            $this->commands([
                GenerateCommand::class,
            ]);
        }
        */
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge default config ///
        /*
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'odoo-api'
        );
        //*/
        // Facade accessor
        $this->app->bind(MenuBuilder::class, function ($app) {
            return new MenuBuilder();
        });
        $this->app->bind(MenuFacadeAcessor::class, function ($app) {
            return new MenuFacadeAcessor();
        });
    }

    public function provides()
    {
        return [
            MenuBuilder::class,
            MenuFacadeAcessor::class,
        ];
    }
}
