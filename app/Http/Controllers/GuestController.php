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
        // Se o usuário estiver autenticado, redirecionar para o DASHBOARD apropriado
        if (auth()->check()) {
            $user = auth()->user();
            $role = $user->getRoleNames()->first();
            
            // Redirecionar para o dashboard baseado no papel
            return $this->redirectToDashboard($role);
        }

        return Inertia::render('Guest/Home', [
            'authRoutes' => [
                'login' => route('auth.login'),
                'register' => route('auth.register'),
            ]
        ]);
    }
    
    /**
     * Redirect user to appropriate dashboard
     */
    private function redirectToDashboard($role): RedirectResponse
    {
        switch ($role) {
            case 'PCA':
                return redirect()->route('pca.dashboard');
            case 'Gestor':
                return redirect()->route('manager.dashboard');
            case 'Técnico':
                return redirect()->route('technician.dashboard');
            case 'Utente':
            default:
                return redirect()->route('user.dashboard');
        }
    }
}