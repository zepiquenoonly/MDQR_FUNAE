<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    
    // Log para debug
    Log::info('ProfileController@edit - Iniciando', [
        'user_id' => $user->id,
        'user_locale' => $user->locale,
        'app_locale' => app()->getLocale(),
        'session_locale' => session()->get('locale'),
        'route' => $request->route()->getName()
    ]);

    // Compartilhar dados comuns
    $this->shareCommonData();

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
            'locale' => $user->locale ?? config('app.locale', 'pt'),
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

        // Log dos dados recebidos
        Log::info( 'ProfileController@update - Dados recebidos', [
            'user_id' => $userId,
            'all_data' => $request->all(),
            'locale_received' => $request->locale,
            'current_user_locale' => $request->user()->locale,
            'session_locale' => session()->get( 'locale' ),
            'app_locale' => app()->getLocale()
        ] );

        // Validar todos os dados de uma vez
        $validated = $request->validate( [
            'name' => [ 'string', 'max:255' ],
            'username' => [ 'string', 'max:255', 'unique:users,username,' . $userId ],
            'email' => [ 'string', 'email', 'max:255', 'unique:users,email,' . $userId ],
            'locale' => ['required', 'in:pt,en,pt_MZ,pt_PT'], 
            'province' => [ 'string', 'max:255' ],
            'district' => [ 'string', 'max:255' ],
            'neighborhood' => [ 'string', 'max:255' ],
            'street' => [ 'nullable', 'string', 'max:255' ],
        ], [
            'name' => 'O campo nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',

            'username' => 'O campo nome de utilizador é obrigatório.',
            'username.string' => 'O nome de utilizador deve ser um texto.',
            'username.max' => 'O nome de utilizador não pode ter mais de 255 caracteres.',
            'username.unique' => 'Este nome de utilizador já está em uso.',

            'email' => 'O campo email é obrigatório.',
            'email.string' => 'O email deve ser um texto.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está em uso.',

            'locale.in' => 'Idioma inválido. Use "pt" ou "en".',

            'province' => 'O campo província é obrigatório.',
            'province.string' => 'A província deve ser um texto.',
            'province.max' => 'A província não pode ter mais de 255 caracteres.',

            'district' => 'O campo distrito é obrigatório.',
            'district.string' => 'O distrito deve ser um texto.',
            'district.max' => 'O distrito não pode ter mais de 255 caracteres.',

            'neighborhood' => 'O campo bairro é obrigatório.',
            'neighborhood.string' => 'O bairro deve ser um texto.',
            'neighborhood.max' => 'O bairro não pode ter mais de 255 caracteres.',

            'street.string' => 'A rua deve ser um texto.',
            'street.max' => 'A rua não pode ter mais de 255 caracteres.',
        ] );

        Log::info( 'ProfileController@update - Validação passada', [
            'validated_data' => $validated,
            'locale_validated' => $validated[ 'locale' ] ?? 'não fornecido'
        ] );

        $user = $request->user();

        // Actualizar email_verified_at se o email mudou
        if ( $user->email !== $validated[ 'email' ] ) {
            $validated[ 'email_verified_at' ] = null;
        }

        // Log antes de atualizar locale
        if ( isset( $validated[ 'locale' ] ) ) {
            Log::info( 'ProfileController@update - Atualizando locale', [
                'old_locale' => $user->locale,
                'new_locale' => $validated[ 'locale' ],
                'locale_changed' => $user->locale !== $validated[ 'locale' ],
                'user_id' => $user->id
            ] );

            // Se locale foi alterado, atualizar sessão e aplicação
            if ( $user->locale !== $validated[ 'locale' ] ) {
                app()->setLocale( $validated[ 'locale' ] );
                session()->put( 'locale', $validated[ 'locale' ] );

                Log::info( 'ProfileController@update - Locale atualizado na sessão e aplicação', [
                    'app_locale_set' => app()->getLocale(),
                    'session_locale_set' => session()->get( 'locale' )
                ] );
            }
        }

        // Log antes de salvar no banco
        Log::info( 'ProfileController@update - Salvando no banco de dados', [
            'updates_to_save' => $validated
        ] );

        // Actualizar todos os dados
        $user->update( $validated );

        // Log após salvar
        Log::info( 'ProfileController@update - Usuário atualizado', [
            'user_locale_after_update' => $user->fresh()->locale,
            'update_success' => true
        ] );

        return Redirect::route( 'profile.info' )->with( 'success', 'Informações do perfil atualizadas com sucesso!' );
    }

    /**
    * Update the user's extended profile information.
     */
     public function updatePassword(Request $request): RedirectResponse
    {
        Log::info('ProfileController@updatePassword - Iniciando', [
            'user_id' => $request->user()->id
        ]);

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

        Log::info('ProfileController@updatePassword - Password atualizada', [
            'user_id' => $request->user()->id,
            'success' => true
        ]);

        return Redirect::route('profile.security')->with('success', 'Password atualizada com sucesso!');
    }


    public function uploadAvatar(Request $request)
{
    Log::info('ProfileController@uploadAvatar - Iniciando', [
        'user_id' => Auth::id(),
        'has_file' => $request->hasFile('avatar')
    ]);

    $validator = Validator::make($request->all(), [
        'avatar' => 'required|image|mimes:jpeg, png, jpg, gif, webp|max:5120',
    ], [
        'avatar.required' => 'Por favor, selecione uma imagem.',
        'avatar.image' => 'O arquivo deve ser uma imagem.',
        'avatar.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou webp.',
        'avatar.max' => 'A imagem não pode ter mais de 5MB.',
    ]);

    if ($validator->fails()) {
        Log::error('ProfileController@uploadAvatar - Validação falhou', [
            'errors' => $validator->errors()->toArray()
        ]);
        return back()->withErrors($validator)->with('error', 'Erro de validação: ' . $validator->errors()->first());
    }

    try {
        $user = Auth::user();
        Log::info('ProfileController@uploadAvatar - Usuário encontrado', [
            'user_id' => $user->id,
            'has_old_avatar' => !empty($user->avatar_path)
        ]);

        // Delete old avatar if exists
        if ($user->avatar_path) {
            Log::info('ProfileController@uploadAvatar - Removendo avatar antigo', [
                'old_path' => $user->avatar_path,
                'file_exists' => Storage::disk('public')->exists($user->avatar_path)
            ]);
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store new avatar
        $file = $request->file('avatar');
        Log::info('ProfileController@uploadAvatar - Arquivo recebido', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension()
        ]);

        // Ensure avatars directory exists
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
            Log::info('ProfileController@uploadAvatar - Diretório avatars criado');
        }

        $fileName = 'avatar-' . $user->id . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $fileName, 'public');

        Log::info('ProfileController@uploadAvatar - Arquivo armazenado', [
            'path' => $path,
            'full_path' => Storage::disk('public')->path($path)
        ]);

        // Update user record
        $user->update([
            'avatar_path' => $path,
            'avatar_url' => Storage::disk('public')->url($path)
        ]);

        Log::info('ProfileController@uploadAvatar - Usuário atualizado', [
            'new_avatar_path' => $user->avatar_path,
            'new_avatar_url' => $user->avatar_url
        ]);

        return back()->with([
            'success' => 'Foto de perfil atualizada com sucesso!',
            'avatar_url' => $user->avatar_url
        ]);

    } catch (\Exception $e) {
        Log::error('ProfileController@uploadAvatar - Erro', [
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'user_id' => Auth::id()
        ]);
        return back()->with('error', 'Erro ao fazer upload da foto: ' . $e->getMessage());
    }
}



    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request)
{
    Log::info('ProfileController@deleteAvatar - Iniciando', [
        'user_id' => Auth::id()
    ]);

    try {
        $user = Auth::user();

        if ($user->avatar_path) {
            Log::info('ProfileController@deleteAvatar - Removendo avatar', [
                'avatar_path' => $user->avatar_path,
                'file_exists' => Storage::disk('public')->exists($user->avatar_path)
            ]);
            
            Storage::disk('public')->delete($user->avatar_path);
            
            $user->update([
                'avatar_path' => null,
                'avatar_url' => null
            ]);
            
            Log::info('ProfileController@deleteAvatar - Avatar removido com sucesso');
        } else {
            Log::info('ProfileController@deleteAvatar - Usuário não tem avatar');
        }

        return back()->with('success', 'Foto de perfil removida com sucesso!');

    } catch (\Exception $e) {
        Log::error('ProfileController@deleteAvatar - Erro', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Erro ao remover a foto: ' . $e->getMessage());
    }
}
    /**
     * Get user avatar
     */
   public function getAvatar(Request $request)
    {
        $user = $request->user();
        
        Log::info('ProfileController@getAvatar', [
            'user_id' => $user->id,
            'has_avatar' => !empty($user->avatar_url)
        ]);

        return response()->json([
            'avatar_url' => $user->avatar_url
        ]);
    }


    /**
     * Delete the user's account.
    */

    public function destroy( Request $request ): RedirectResponse {
        Log::info( 'ProfileController@destroy - Iniciando exclusão de conta', [
            'user_id' => $request->user()->id
        ] );

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

        Log::info( 'ProfileController@destroy - Conta excluída com sucesso', [
            'user_id_deleted' => $user->id
        ] );

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

    /**
    * Debug endpoint para verificar estado do sistema
    */

    public function debug( Request $request ) {
        $user = $request->user();

        $debugInfo = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'locale' => $user->locale,
                'locale_in_database' => $user->getRawOriginal( 'locale' ) ?? 'NULL',
            ],
            'application' => [
                'locale' => app()->getLocale(),
                'fallback_locale' => config( 'app.fallback_locale' ),
                'available_locales' => config( 'app.available_locales', [ 'pt', 'en' ] ),
            ],
            'session' => [
                'locale' => session()->get( 'locale' ),
                'all_session' => session()->all(),
            ],
            'request' => [
                'locale_from_request' => $request->locale,
                'all_input' => $request->all(),
                'method' => $request->method(),
            ],
            'environment' => [
                'app_env' => config( 'app.env' ),
                'app_debug' => config( 'app.debug' ),
            ]
        ];

        Log::info( 'ProfileController@debug - Informações de debug', $debugInfo );

        return response()->json( $debugInfo );
    }

    public function debugLocale(Request $request)
{
    return response()->json([
        'user_locale' => $request->user()->locale,
        'session_locale' => session('locale'),
        'app_locale' => app()->getLocale(),
        'available_locales' => config('app.available_locales'),
        'translations_loaded' => count(__('messages') ?: []),
    ]);
}
}