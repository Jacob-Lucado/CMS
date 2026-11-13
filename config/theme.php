<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Active Theme
    |--------------------------------------------------------------------------
    |
    | It will assign the default active theme to be used if one is not set during
    | runtime.
    */
    'active' => 'atom',

    /*
    |--------------------------------------------------------------------------
    | Parent Theme
    |--------------------------------------------------------------------------
    |
    | This is a parent theme for the theme specified in the active config
    | option. It works like the WordPress style theme hierarchy, if the blade
    | file is not found in the currently active theme, then it will look for it
    | in the parent theme.
    */
    'parent' => 'atom',

    /*
    |--------------------------------------------------------------------------
    | Pinned Theme
    |--------------------------------------------------------------------------
    |
    | A theme set here wins over the "theme" website setting and over the
    | --theme option of atom:install, so an installer that asks for another
    | theme still gets this one. Leave it empty to pick the theme in the
    | website settings instead.
    |
    */

    'pinned' => env('CMS_THEME'),

    /*
    |--------------------------------------------------------------------------
    | Base Path
    |--------------------------------------------------------------------------
    |
    | The base path where all the themes are located.
    */
    'base_path' => base_path('resources/themes'),
];
