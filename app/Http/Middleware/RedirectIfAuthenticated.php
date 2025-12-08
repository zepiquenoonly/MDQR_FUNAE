<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Verificar se é uma rota de autenticação/login
                if ($this->isAuthRoute($request)) {
                    return $this->redirectBasedOnRole();
                }
            }
        }

        return $next($request);
    }

    /**
     * Verificar se a rota atual é uma rota de autenticação/login
     */
    private function isAuthRoute(Request $request): bool
    {
        return $request->routeIs([
            'auth.main',
            'auth.login',
            'auth.register',
            'auth.register.complete',
            'login',
            'register',
            'password.request',
            'password.reset'
        ]) || in_array($request->path(), [
            'login',
            'register',
            'auth',
            'register/complete'
        ]);
    }

    /**
     * Redirecionar baseado no papel do usuário
     */
    private function redirectBasedOnRole()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

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
