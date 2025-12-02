<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    /**
     * Display the home page for guests
     * Redirect authenticated users to their dashboard
     */
    public function home(): Response|RedirectResponse
    {
        // Se o usuário estiver autenticado, redirecionar para o dashboard
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return Inertia::render('Guest/Home', [
            'authRoutes' => [
                'login' => route('auth.login'),
                'register' => route('auth.register'),
            ]
        ]);
    }
}