<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '/api/update-profile',
        '/api/book-ticket',
        '/api/bookings',
        'api/*'
    ];
}
