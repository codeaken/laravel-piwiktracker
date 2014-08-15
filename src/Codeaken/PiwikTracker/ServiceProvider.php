<?php

namespace Codeaken\PiwikTracker;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('codeaken/laravel-piwiktracker');

        // Include routes to handle the proxying of piwik requests if we
        // are running in hidden mode
        include __DIR__.'/../../routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the piwik tracker object
        $this->app['piwiktracker'] = $this->app->share(function() {
            return new PiwikTracker();
        });

        // Insert the piwik tracker code when we discover a @piwiktracker
        // directive in a template
        Blade::extend(function($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('piwiktracker');
            return preg_replace($pattern, '<?php echo PiwikTracker::getCode() ?>', $view);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('piwiktracker');
    }
}
