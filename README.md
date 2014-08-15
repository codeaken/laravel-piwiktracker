# Laravel Piwik Tracker
This Laravel 4 package allows you to **insert the Piwik tracking code** in your templates with the new Blade directive `@piwiktracker`.

It also has a feature that will **allow you to hide your Piwik server URL** by using the included proxy. This is great if you do not want anyone to find all of your sites by searching for the Pwik server URL.

## Installation
Require the package in your **composer.json** and then run `composer update`

    "require": {
        ...
        "codeaken/laravel-piwiktracker": "1.*"
        ...
    },

After updating composer, add the Service Provider to the `providers` array in `app/config/app.php`

    'providers' => array(
        ...
        'Codeaken\PiwikTracker\ServiceProvider',
        ...
    ),

Also add the Facade to the `aliases` array in `app/config/app.php`

    'aliases' => array(
        ...
        'PiwikTracker' => 'Codeaken\PiwikTracker\Facade',
        ...
    ),

The tracker is disabled by default so you need to copy the configuration file and enable it

    $ php artisan config:publish codeaken/laravel-piwiktracker

## Configuration file
* `enabled`: Allows you to enable or disable the insertion of the tracking code. Useful for when you are developing your site and dont need the visits tracked. Default is `false`.

* `site_id`: Id of the site you are tracking. You can find this id in the Piwik settings under Tracking Code. Default is `0`.

* `tracker_url`: URL to the Piwik server. Should only be the hostname without the schema, for example **mypiwik.com**. You can add an additional path if your have installed the Piwik server in a sub-directory, for example **mypiwik.com/piwik**.  Default is an empty string.

* `hidden`: Set this to true if you want to hide the Piwik server url. We will then proxy all the requests to the `tracker_url`. This requires that you set a Piwik authorization token in the `token` option. Default is `false`.

* `token`: A Piwik authorization token for a user that has access to the site that you want to track. Only used if `hidden` is set to `true`. Default is an empty string.

## Adding the tracking code
In your layout (or any other template) add the new Blade directive `@piwiktracker` before the `</body>` tag and the tracking code should display if you have set `enabled` to `true` in the configuration file (and also set a valid `site_id` and `tracker_url`).

## Hidden tracker URL
The Piwik Tracker Package has a feature of **hiding the Piwik server URL** (`tracker_url` in the config). You enable it by setting `hidden` to `true` and adding a valid token.

The request for the Piwik javascript will then go to the route `piwiktracker/js` and we will fetch the javascript (and cache it) from the Piwik server and return it.

The actual tracking call will go to `piwiktracker/php` where we add the pageview via the Piwik API by using the `token` in the config. We also return the 1x1 gif file.

## License
Laravel Piwik Tracker is licensed under the [MIT License](http://opensource.org/licenses/MIT).

Copyright 2014 Magnus Johansson