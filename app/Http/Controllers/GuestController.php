<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    /**
     * Display the home page
     * Accessible to both authenticated and guest users
     */
    public function home(): Response
    {
        return Inertia::render('Guest/Home', [
            'authRoutes' => [
                'login' => route('login'),
                'register' => route('auth.register'),
            ],
            'isAuthenticated' => auth()->check(),
            'user' => auth()->check() ? [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->getRoleNames()->first(),
            ] : null,
        ]);
    }
}
