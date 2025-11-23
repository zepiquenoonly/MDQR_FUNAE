<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    /**
     * Display the home page for guests
     */
    public function home(): Response
    {
        return Inertia::render('Guest/Home', [
            'authRoutes' => [
                'login' => route('auth.login'),
                'register' => route('auth.register'),
            ]
        ]);
    }
}