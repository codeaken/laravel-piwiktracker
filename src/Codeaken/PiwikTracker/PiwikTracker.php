<?php

namespace Codeaken\PiwikTracker;

use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class PiwikTracker
{
    private $siteId;
    private $trackerUrl;
    private $enabled;
    private $hidden;
    private $token;
    private $cacheLifetime = 1440;

    public function __construct()
    {
        // Extract the config
        $this->siteId     = (int)Config::get('laravel-piwiktracker::site_id', 0);
        $this->trackerUrl = trim(Config::get('laravel-piwiktracker::tracker_url', ''));
        $this->enabled    = Config::get('laravel-piwiktracker::enabled', false);
        $this->hidden     = Config::get('laravel-piwiktracker::hidden', false);
        $this->token      = trim(Config::get('laravel-piwiktracker::token', ''));

        // Normalize the tracker url
        $this->trackerUrl = str_replace(
            ['http://', 'https://'],
            '',
            $this->trackerUrl
        );
        $this->trackerUrl = rtrim($this->trackerUrl, '/');

        // Validate the config
        if ( ! is_bool($this->enabled)) {
            throw new \InvalidArgumentException("PiwikTracker configuration option 'enabled' must be of a boolean type");
        }

        if ( ! is_bool($this->hidden)) {
            throw new \InvalidArgumentException("PiwikTracker configuration option 'hidden' must be of a boolean type");
        }

        if ($this->enabled) {

            if ( ! is_numeric($this->siteId) || empty($this->siteId)) {
                throw new \InvalidArgumentException("PiwikTracker configuration option 'site_id' must be a non-zero integer");
            }

            if (empty($this->trackerUrl)) {
                throw new \InvalidArgumentException("PiwikTracker configuration option 'tracker_url' must be a valid url");
            }

            if ($this->hidden) {

                // If we are running in hidden mode we need a piwik
                // authorization token
                if (empty($this->token) || strlen($this->token) != 32) {
                    throw new \InvalidArgumentException("PiwikTracker configuration option 'token' must be a valid piwik authorization token when running in hidden mode");
                }
            }
        }
    }

    public function getJs()
    {
        // If we already have the javascript code in the cache, return it
        if (Cache::has('piwikjs')) {
            return Cache::get('piwikjs');
        }

        // Download the javascript code, cache it, and return it
        try {
            $js = file_get_contents("http://{$this->trackerUrl}/piwik.js");

            Cache::put('piwikjs', $js, $this->cacheLifetime);

            return $js;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function proxy()
    {
        $request = Request::createFromGlobals();

        $ip = $request->getClientIp();
        $ua = $request->server->get('HTTP_USER_AGENT', 'PiwikTrackerProxy');
        $al = $request->server->get('HTTP_ACCEPT_LANGUAGE', '');

        // Assemble the query string
        $qs  = $request->getQueryString();
        $qs .= "&cip={$ip}&token_auth={$this->token}";

        // Assemble the final url
        $url = "http://{$this->trackerUrl}/piwik.php?{$qs}";

        // Setup a stream context
        $streamOptions = [
            'http' => [
                'user_agent' => $ua,
                'header'     => "Accept-Language: $al\r\n",
                'timeout'    => 5
            ]
        ];
        $streamContext = stream_context_create($streamOptions);

        // Call piwik to register the pageview
        try {
            return file_get_contents($url, 0, $streamContext);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function getCode()
    {
        // If we have disabled the tracker then just return an empty string
        if ( ! $this->enabled) {
            return '';
        }

        // We need to change some parameters in the tracking code depending on
        // if we run in hidden mode or not
        if ($this->hidden) {
            $trackerUrl = str_replace(['http://', 'https://'], '', URL::to('/'));
            $piwikPhp   = 'piwiktracker/php';
            $piwikJs    = 'piwiktracker/js';
        } else {
            $trackerUrl = $this->trackerUrl;
            $piwikPhp   = 'piwik.php';
            $piwikJs    = 'piwik.js';
        }

        // Assemble the tracking code
        $codeTemplate = "
            <script type=\"text/javascript\">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
            var u=(('https:' == document.location.protocol) ? 'https' : 'http') + '://{$trackerUrl}';
            _paq.push(['setTrackerUrl', u+'/{$piwikPhp}']);
            _paq.push(['setSiteId', {$this->siteId}]);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
            g.defer=true; g.async=true; g.src=u+'/{$piwikJs}'; s.parentNode.insertBefore(g,s);
            })();
            </script>";

        return $codeTemplate;
    }
}
