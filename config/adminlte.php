<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The default title of your admin panel, this goes into the title tag
    | of your page. You can override it per page with the title section.
    | You can optionally also specify a title prefix and/or postfix.
    |
    */

    'title' => 'Race Timing Solutions | GPS Tracking',

    'title_prefix' => '',

    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | This logo is displayed at the upper left corner of your admin panel.
    | You can use basic HTML here if you want. The logo has also a mini
    | variant, used for the mini side bar. Make it 3 letters or so
    |
    */

    'logo' => '<b>RTS</b> | GPS Tracking',

    'logo_mini' => '<b>RTS</b>',

    /*
    |--------------------------------------------------------------------------
    | Skin Color
    |--------------------------------------------------------------------------
    |
    | Choose a skin color for your admin panel. The available skin colors:
    | blue, black, purple, yellow, red, and green. Each skin also has a
    | ligth variant: blue-light, purple-light, purple-light, etc.
    |
    */

    'skin' => 'blue',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Choose a layout for your admin panel. The available layout options:
    | null, 'boxed', 'fixed', 'top-nav'. null is the default, top-nav
    | removes the sidebar and places your menu in the top navbar
    |
    */

    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | Collapse Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we choose and option to be able to start with a collapsed side
    | bar. To adjust your sidebar layout simply set this  either true
    | this is compatible with layouts except top-nav layout option
    |
    */

    'collapse_sidebar' => false,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Register here your dashboard, logout, login and register URLs. The
    | logout URL automatically sends a POST request in Laravel 5.3 or higher.
    | You can set the request to a GET or POST with logout_method.
    | Set register_url to null if you don't want a register link.
    |
    */

    'dashboard_url' => 'home',

    'logout_url' => 'logout',

    'logout_method' => 'GET',

    'login_url' => 'auth/login',

    'register_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Specify your menu items to display in the left sidebar. Each menu item
    | should have a text and and a URL. You can also specify an icon from
    | Font Awesome. A string instead of an array represents a header in sidebar
    | layout. The 'can' is a filter on Laravel's built in Gate functionality.
    |
    */

    'menu' => [
        'HOME',
        [
            'text'        => 'Latest News',
            'url'         => 'news',
            'icon'        => 'newspaper-o',
            // 'label'       => 4,
            // 'label_color' => 'success',
        ],
        [
            'text'        => 'Upcoming Events',
            'url'         => 'upcoming-events',
            'icon'        => 'newspaper-o',
            // 'label'       => 4,
            // 'label_color' => 'success',
        ],
        'CALENDAR',
        [
            'text'        => 'Calendar',
            'url'         => 'calendar',
            'icon'        => 'calendar',
            // 'label'       => 4,
            // 'label_color' => 'success',
        ],
        'CHAPTERS',
        [
            'text' => 'Chapter 1',
            'url'  => 'chapter/1',
            'icon' => 'file',
        ],
        [
            'text' => 'Chapter 2',
            'url'  => 'chapter/2',
            'icon' => 'file',
        ],
        [
            'text' => 'Chapter 3',
            'url'  => 'chapter/3',
            'icon' => 'file',
        ],
        [
            'text' => 'Chapter 4',
            'url'  => 'chapter/4',
            'icon' => 'file',
        ],
        [
            'text' => 'Chapter 5',
            'url'  => 'chapter/5',
            'icon' => 'file'
        ],
        'ARTISTS/PARTNERS',
        [
            'text' => 'Artists',
            'url'  => 'artists',
            'icon' => 'users',
        ],
        [
            'text' => 'Partners',
            'url'  => 'partners',
            'icon' => 'users',
        ],
        // [
        //     'text'    => 'Multilevel',
        //     'icon'    => 'share',
        //     'submenu' => [
        //         [
        //             'text' => 'Level One',
        //             'url'  => '#',
        //         ],
        //         [
        //             'text'    => 'Level One',
        //             'url'     => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'Level Two',
        //                     'url'  => '#',
        //                 ],
        //                 [
        //                     'text'    => 'Level Two',
        //                     'url'     => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'Level Three',
        //                             'url'  => '#',
        //                         ],
        //                         [
        //                             'text' => 'Level Three',
        //                             'url'  => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'Level One',
        //             'url'  => '#',
        //         ],
        //     ],
        // ]
        // ,
        // 'LABELS',
        // [
        //     'text'       => 'Important',
        //     'icon_color' => 'red',
        // ],
        // [
        //     'text'       => 'Warning',
        //     'icon_color' => 'yellow',
        // ],
        // [
        //     'text'       => 'Information',
        //     'icon_color' => 'aqua',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Choose which JavaScript plugins should be included. At this moment,
    | only DataTables is supported as a plugin. Set the value to true
    | to include the JavaScript file from a CDN via a script tag.
    |
    */

    'plugins' => [
        'datatables' => true,
        'select2'    => true,
        'chartjs'    => true,
        'inputmask'    => true,
        'ckeditor'    => true,
        'jqueryui'    => true,
        'lightgallery'    => true,
    ],
];
