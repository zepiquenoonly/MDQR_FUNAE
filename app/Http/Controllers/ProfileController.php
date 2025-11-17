<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        // Determinar qual aba está ativa baseado na rota
        $activeTab = 'info';
        $currentRoute = $request->route()->getName();
        
        if ($currentRoute === 'profile.security') {
            $activeTab = 'security';
        } elseif ($currentRoute === 'profile.notifications') {
            $activeTab = 'notifications';
        } elseif ($currentRoute === 'profile.preferences') {
            $activeTab = 'preferences';
        }
        
        // Estatísticas para o dashboard do perfil - VALORES TEMPORÁRIOS
        $stats = [
            'suggestions' => 0,
            'complaints' => 0,
            'reclamations' => 0,
        ];

        return Inertia::render('Profile/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username ?? '',
                'role' => $user->getRoleNames()->first() ?? 'Utente',
                'phone' => $user->phone ?? '',
                'province' => $user->province ?? '',
                'district' => $user->district ?? '',
                'neighborhood' => $user->neighborhood ?? '',
                'street' => $user->street ?? '',
                'initials' => $this->getInitials($user->name),
                'email_verified_at' => $user->email_verified_at,
            ],
            'stats' => $stats,
            'activeTab' => $activeTab
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validar todos os dados de uma vez
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $request->user()->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        // Atualizar email_verified_at se o email mudou
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        // Atualizar todos os dados
        $user->update($validated);

        return Redirect::route('profile.info')->with('status', 'Informações do perfil atualizadas com sucesso!');
    }

    /**
     * Update the user's extended profile information.
     */
    public function updateExtended(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return Redirect::route('profile.info')->with('status', 'Informações de localização atualizadas com sucesso!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.security')->with('status', 'Password atualizada com sucesso!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Get user initials for avatar
     */
    private function getInitials($name)
    {
        $names = explode(' ', $name);
        $initials = '';
        
        if (count($names) >= 2) {
            $initials = strtoupper($names[0][0] . $names[count($names)-1][0]);
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }
        
        return $initials;
    }
}