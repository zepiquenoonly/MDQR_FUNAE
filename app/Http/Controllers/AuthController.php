<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller {
    /**
    * Display the main authentication page.
    */

    public function showMain(): Response {
        return Inertia::render( 'Auth/Main', [
            'flash' => [
                'success' => session( 'success' ),
                'error' => session( 'error' )
            ]
        ] );
    }

    /**
    * Display the login form.
    */

    public function showLogin(): Response {
        return Inertia::render( 'Auth/Main', [
            'initialPanel' => 'login',
            'flash' => [
                'success' => session( 'success' ),
                'error' => session( 'error' )
            ]
        ] );
    }

    /**
    * Display the registration form.
    */

    public function showRegister(): Response {
        return Inertia::render( 'Auth/Main', [
            'initialPanel' => 'register',
            'flash' => [
                'success' => session( 'success' ),
                'error' => session( 'error' )
            ]
        ] );
    }

    /**
    * Handle user login.
    */

    public function login( Request $request ) {
        $credentials = $request->validate( [
            'username' => 'required|string',
            'password' => 'required',
        ] );

        if ( Auth::attempt( $credentials, $request->boolean( 'remember' ) ) ) {
            $request->session()->regenerate();

            // Redirecionar baseado no role
            return $this->redirectBasedOnRole();
        }

        // Retornar erro genérico para segurança
        return back()->withErrors( [
            'auth_error' => 'Falha na autenticação, usuário ou senha inválidos.',
        ] )->onlyInput( 'username' );
    }

    /**
    * Handle user registration.
    */

    public function register( Request $request ): RedirectResponse {
        $validator = Validator::make( $request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min( 8 )
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ], [
            'password.mixed' => 'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um símbolo.',
        ] );

        if ( $validator->fails() ) {
            return back()->withErrors( $validator )->withInput();
        }

        // Store basic registration data in session
        session( [
            'basic_registration' => [
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make( $request->password ),
            ]
        ] );

        return redirect()->route( 'auth.register.complete' );
    }

    /**
    * Display the complete registration form.
    */

    public function showCompleteRegistration(): Response|RedirectResponse {
        $basicData = session( 'basic_registration' );

        if ( !$basicData ) {
            return redirect()->route( 'auth.register' );
        }

        return Inertia::render( 'Auth/CompleteRegistration', [
            'basicData' => $basicData,
            'flash' => [
                'success' => session( 'success' ),
                'error' => session( 'error' )
            ]
        ] );
    }

    public function completeRegistration( Request $request ): RedirectResponse {
        try {
            // Recupera os dados básicos da sessão
            $basicData = session( 'basic_registration' );

            if ( !$basicData ) {
                return redirect()->route( 'auth.register' )->with( 'error', 'Sessão expirada. Por favor, registre-se novamente.' );
            }

            // Validação dos dados
            $validated = $request->validate( [
                'nome' => 'required|string|max:255',
                'apelido' => 'required|string|max:255',
                'celular' => 'required|string|max:255',
                'provincia' => 'required|string|max:255',
                'distrito' => 'required|string|max:255',
                'bairro' => 'required|string|max:255',
                'rua' => 'nullable|string|max:255',
            ] );

            // Create user with complete data
            $user = User::create( [
                'name' => $request->nome . ' ' . $request->apelido,
                'username' => $basicData[ 'username' ],
                'email' => $basicData[ 'email' ],
                'password' => $basicData[ 'password' ],
                'phone' => $request->celular,
                'province' => $request->provincia,
                'district' => $request->distrito,
                'neighborhood' => $request->bairro,
                'street' => $request->rua,
            ] );

            // Handle document uploads if any
            if ( $request->hasFile( 'documents' ) ) {
                foreach ( $request->file( 'documents' ) as $document ) {
                    $path = $document->store( 'user_documents', 'public' );
                    // You might want to create an Attachment model to store these
                }
            }

            // Assign default role to new users
            $user->assignRole( 'Utente' );

            // Clear session data
            session()->forget( 'basic_registration' );

            // Login automático após registro
            Auth::login( $user );

            return redirect()->route( 'user.dashboard' )->with( 'success', 'Registro realizado com sucesso!' );

        } catch ( \Exception $e ) {
            return back()->with( 'error', 'Erro ao completar registro: ' . $e->getMessage() )->withInput();
        }
    }

    /**
    * Redirect user based on their role
    */

    private function redirectBasedOnRole() {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        switch ( $role ) {
            case 'Admin':
            case 'Super Admin':
                return redirect()->route( 'admin.dashboard' );
            case 'PCA':
                return redirect()->route( 'pca.dashboard' );
            case 'Gestor':
                return redirect()->route( 'gestor.manager.dashboard' );
            case 'Técnico':
                return redirect()->route( 'technician.dashboard' );
            case 'Director':
                return redirect()->route( 'director.dashboard' );
            case 'Utente':
            default:
                return redirect()->route( 'user.dashboard' );
        }
    }

    /**
    * Display the home page for authenticated users.
    */

    public function home(): RedirectResponse {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        //return $this->redirectToDashboard( $role );
        return $this->redirectBasedOnRole();
    }

    /**
    * Get the appropriate dashboard based on user role
    */

    public function showProject( $projectId ): Response {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        return Inertia::render( 'Utente/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'created_at' => $user->created_at->format( 'd/m/Y' ),
            ],
            'projectId' => ( int ) $projectId
        ] );
    }

    /**
    * Handle user logout.
    */

    public function logout( Request $request ): RedirectResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Use a rota correta que existe no seu web.php
        return redirect()->route( 'auth.main' );
        // ou 'auth.login'
    }
}
