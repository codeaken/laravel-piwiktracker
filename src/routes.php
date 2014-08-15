<?php

Route::get('/piwiktracker/js', function()
{
    $js = PiwikTracker::getJs();

    if (false === $js) {
        // We failed to get the javascript code
        return App::abort(500);
    }

    return Response::make(
        $js,
        200,
        [
            'Content-Type' => 'application/javascript',
        ]
    );
});

Route::get('/piwiktracker/php', function()
{
    $gif = PiwikTracker::proxy();

    if (false === $gif) {
        // We failed to call the piwik tracker
        return App::abort(500);
    }

    return Response::make($gif, 200, ['Content-Type' => 'image/gif']);
});
