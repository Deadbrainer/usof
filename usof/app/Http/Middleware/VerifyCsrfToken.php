<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/users',
        'api/auth/login',
        'api/auth/logout',
        'api/auth/register',
        'api/users/*',
        'api/users/avatar',

        'api/posts',
        'api/posts/*/comments',
        'api/posts/*/like',
        'api/posts/*/dislike',
        'api/posts/*',
        'api/posts/*/categories',

        'api/categories',
        'api/categories/*',
        'api/categories/*/posts',

        'api/comments/*',
        'api/comments/*/like'
    ];
}
