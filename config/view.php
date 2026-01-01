<?php

/**
 * View Configuration
 *
 * Configure default layout, layout search paths, and view settings.
 */

return [
    /**
     * Default Layout
     *
     * The default layout file to use when rendering views.
     * This will be looked up in the layout_paths below.
     *
     * Set to null to disable default layout.
     *
     * Examples:
     * - 'app' -> looks for layouts/app.php
     * - 'main' -> looks for layouts/main.php
     */
    'default_layout' => env('VIEW_DEFAULT_LAYOUT', 'app'),

    /**
     * Layout Search Paths
     *
     * Directories to search for layout files, relative to app/Views/.
     * The framework will search these paths in order when looking for layouts.
     *
     * Examples:
     * - 'layouts' -> app/Views/layouts/
     * - 'admin/layouts' -> app/Views/admin/layouts/
     */
    'layout_paths' => [
        'layouts',
        'admin/layouts',
    ],

    /**
     * Views Base Path
     *
     * The base directory for view files.
     * Typically you don't need to change this.
     */
    'base_path' => base_path('app/Views'),


];
