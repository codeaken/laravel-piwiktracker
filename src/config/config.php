<?php

return [
    // Enable or Disable the tracking code to be inserted.
    'enabled'     => false,

    // Id of the site you are tracking. You will find this id in the Piwik
    // admin in settings under Tracking Code.
    'site_id'     => 0,

    // URL to the Piwik tracking server. Should only be the hostname without
    // the scheme (http/https). If you have installed Piwik in a sub-directory
    // you will need to include this also. For example, example.com/piwik.
    'tracker_url' => '',

    // If this is set to true we will hide the tracker url and proxy the
    // requests to the tracker. This requires a valid authorization token
    // for a user that has access to the site.
    'hidden'      => false,

    // A Piwik authorization token for a user that has access to the site that
    // you want to track. Only used if hidden is set to true.
    'token'       => '',
];
