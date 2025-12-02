<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller {
    /**
    * Display the user's profile form.
     */
   public function edit(Request $request): Response
{
    $user = $request->user();
    
    $activeTab = 'info';
    $currentRoute = $request->route()->getName();
    
    if ($currentRoute === 'profile.security') {
        $activeTab = 'security';
    } elseif ($currentRoute === 'profile.notifications') {
        $activeTab = 'notifications';
    } elseif ($currentRoute === 'profile.preferences') {
        $activeTab = 'preferences';
    }
    
    $userRole = $user->getRoleNames()->first() ?? 'Utente';
    $showStats = $userRole === 'Utente';
    
    $stats = $showStats ? [
        'suggestions' => 0,
        'complaints' => 0,
        'reclamations' => 0,
    ] : null;

    return Inertia::render('Profile/Profile', [
        'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        'status' => session('status'),
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username ?? '',
            'role' => $userRole,
            'phone' => $user->phone ?? '',
            'province' => $user->province ?? '',
            'district' => $user->district ?? '',
            'neighborhood' => $user->neighborhood ?? '',
            'street' => $user->street ?? '',
            'initials' => $this->getInitials($user->name),
            'email_verified_at' => $user->email_verified_at,
            'avatar_url' => $user->avatar_url ?? null,
        ],
        'stats' => $stats,
        'showStats' => $showStats,
        'activeTab' => $activeTab,
        'flash' => [
            'success' => session('success'),
            'error' => session('error'),
            'avatar_url' => session('avatar_url'),
        ]
    ]);
}

    /**
     * Update the user's profile information.
    */

    public function update( Request $request ): RedirectResponse {
        $userId = $request->user()->id;

        // Validar todos os dados de uma vez
        $validated = $request->validate( [
            'name' => [ 'required', 'string', 'max:255' ],
            'username' => [ 'required', 'string', 'max:255', 'unique:users,username,' . $userId ],
            'email' => [ 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId ],
            'phone' => [
                'nullable',
                'regex:/^\+258\s(8[2-7])\s\d{3}\s\d{4}$/'
            ],
            'province' => [ 'required', 'string', 'max:255' ],
            'district' => [ 'required', 'string', 'max:255' ],
            'neighborhood' => [ 'required', 'string', 'max:255' ],
            'street' => [ 'nullable', 'string', 'max:255' ],
        ], [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',

            'username.required' => 'O campo nome de utilizador é obrigatório.',
            'username.string' => 'O nome de utilizador deve ser um texto.',
            'username.max' => 'O nome de utilizador não pode ter mais de 255 caracteres.',
            'username.unique' => 'Este nome de utilizador já está em uso.',

            'email.required' => 'O campo email é obrigatório.',
            'email.string' => 'O email deve ser um texto.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está em uso.',

            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.string' => 'O telefone deve ser um texto.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'phone.regex' => 'O número de telefone deve estar no formato +258 XX XXX XXXX e começar com 82–87.',

            'province.required' => 'O campo província é obrigatório.',
            'province.string' => 'A província deve ser um texto.',
            'province.max' => 'A província não pode ter mais de 255 caracteres.',

            'district.required' => 'O campo distrito é obrigatório.',
            'district.string' => 'O distrito deve ser um texto.',
            'district.max' => 'O distrito não pode ter mais de 255 caracteres.',

            'neighborhood.required' => 'O campo bairro é obrigatório.',
            'neighborhood.string' => 'O bairro deve ser um texto.',
            'neighborhood.max' => 'O bairro não pode ter mais de 255 caracteres.',

            'street.string' => 'A rua deve ser um texto.',
            'street.max' => 'A rua não pode ter mais de 255 caracteres.',
        ] );

        $user = $request->user();

        // Atualizar email_verified_at se o email mudou
        if ( $user->email !== $validated[ 'email' ] ) {
            $validated[ 'email_verified_at' ] = null;
        }

        // Atualizar todos os dados
        $user->update( $validated );

        return Redirect::route( 'profile.info' )->with( 'success', 'Informações do perfil atualizadas com sucesso!' );
    }

    /**
    * Update the user's extended profile information.
     */
     public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'A password atual é obrigatória.',
            'current_password.current_password' => 'A password atual está incorreta.',
            
            'password.required' => 'A nova password é obrigatória.',
            'password.confirmed' => 'A confirmação da password não corresponde.',
            'password.min' => 'A password deve ter pelo menos 8 caracteres.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.security')->with('success', 'Password atualizada com sucesso!');
    }


    public function uploadAvatar(Request $request)
{
    \Log::info('Upload avatar iniciado', ['user_id' => Auth::id()]);

    $validator = Validator::make($request->all(), [
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ], [
        'avatar.required' => 'Por favor, selecione uma imagem.',
        'avatar.image' => 'O arquivo deve ser uma imagem.',
        'avatar.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou webp.',
        'avatar.max' => 'A imagem não pode ter mais de 5MB.',
    ]);

    if ($validator->fails()) {
        \Log::error('Validação falhou', ['errors' => $validator->errors()]);
        return back()->withErrors($validator)->with('error', 'Erro de validação: ' . $validator->errors()->first());
    }

    try {
        $user = Auth::user();
        \Log::info('Usuário encontrado', ['user_id' => $user->id]);

        // Delete old avatar if exists
        if ($user->avatar_path) {
            \Log::info('Removendo avatar antigo', ['path' => $user->avatar_path]);
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store new avatar
        $file = $request->file('avatar');
        \Log::info('Arquivo recebido', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);

        // Ensure avatars directory exists
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }

        $fileName = 'avatar-' . $user->id . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $fileName, 'public');
        
        \Log::info('Arquivo armazenado', ['path' => $path]);

        // Update user record
        $user->update([
            'avatar_path' => $path,
            'avatar_url' => Storage::disk('public')->url($path)
        ]);

        \Log::info('Usuário atualizado', [
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_url
        ]);

        return back()->with([
            'success' => 'Foto de perfil atualizada com sucesso!',
            'avatar_url' => $user->avatar_url
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro no upload do avatar', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Erro ao fazer upload da foto: ' . $e->getMessage());
    }
}



    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request)
{
    try {
        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update([
                'avatar_path' => null,
                'avatar_url' => null
            ]);
        }

        return back()->with('success', 'Foto de perfil removida com sucesso!');

    } catch (\Exception $e) {
        \Log::error('Erro ao remover avatar', ['error' => $e->getMessage()]);
        return back()->with('error', 'Erro ao remover a foto: ' . $e->getMessage());
    }
}
    /**
     * Get user avatar
     */
   public function getAvatar(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'avatar_url' => $user->avatar_url
        ]);
    }


    /**
     * Delete the user's account.
    */

    public function destroy( Request $request ): RedirectResponse {
        $request->validate( [
            'password' => [ 'required', 'current_password' ],
        ], [
            'password.required' => 'A password é obrigatória.',
            'password.current_password' => 'A password está incorreta.',
        ] );

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to( '/' );
    }

    /**
    * Get user initials for avatar
    */

    private function getInitials( $name ) {
        $names = explode( ' ', $name );
        $initials = '';

        if ( count( $names ) >= 2 ) {
            $initials = strtoupper( $names[ 0 ][ 0 ] . $names[ count( $names )-1 ][ 0 ] );
        } else {
            $initials = strtoupper( substr( $name, 0, 2 ) );
        }

        return $initials;
    }
}