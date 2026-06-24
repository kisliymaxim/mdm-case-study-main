<?php

declare(strict_types=1);

use App\Mdm\Jamf\JamfProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Default MDM provider
    |--------------------------------------------------------------------------
    |
    | The provider key used when none is explicitly passed to a sync trigger.
    | Must exist in the "providers" map below.
    |
    */
    'default' => env('MDM_DEFAULT_PROVIDER', 'jamf'),

    /*
    |--------------------------------------------------------------------------
    | Registered providers
    |--------------------------------------------------------------------------
    |
    | Map of provider key => MdmProvider implementation class. Adding a new
    | provider is a one-line change here plus a class that implements
    | App\Mdm\Contracts\MdmProvider.
    |
    */
    'providers' => [
        'jamf' => JamfProvider::class,
    ],
];
