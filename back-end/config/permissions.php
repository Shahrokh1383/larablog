<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Roles
    |--------------------------------------------------------------------------
    |
    | List of all roles that can be assigned to users.
    | This array is used for validation and authorization checks.
    |
    */
    'roles' => ['admin', 'editor', 'author', 'user'],

    /*
    |--------------------------------------------------------------------------
    | Admin Access Roles
    |--------------------------------------------------------------------------
    |
    | Roles that are allowed to log into the admin panel.
    |
    */
    'admin_roles' => ['admin', 'editor', 'author'],
];