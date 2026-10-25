<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Project
    |--------------------------------------------------------------------------
    |
    | The Hetzner Cloud project used when no project is given, for example
    | when calling HetznerLaravel::servers() or injecting the Client.
    |
    */

    'default' => env('HETZNER_PROJECT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    |
    | Every Hetzner Cloud API token belongs to exactly one project. Add an
    | entry for each project you want to manage and use it with
    | HetznerLaravel::project('name').
    |
    */

    'projects' => [

        'default' => [
            'token' => env('HETZNER_TOKEN'),
            'base_url' => env('HETZNER_BASE_URL', 'https://api.hetzner.cloud'),
        ],

        // 'staging' => [
        //     'token' => env('HETZNER_STAGING_TOKEN'),
        // ],

    ],

];
