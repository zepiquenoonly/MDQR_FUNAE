<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

class GuestController extends Controller
{
    /**
     * Mostra a página inicial pública
     */
    public function home()
    {
        return Inertia::render('Guest/Home', [
            'authRoutes' => [
                'login' => route('auth.login'),
                'register' => route('auth.register'),
            ]
        ]);
    }
}