<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the main authentication page.
     */
    public function showMain(): Response
    {
        return Inertia::render('Auth/Main', [
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    /**
     * Display the login form.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Main', [
            'initialPanel' => 'login',
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    /**
     * Display the registration form.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Main', [
            'initialPanel' => 'register',
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }

        return back()->withErrors([
            'username' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('username');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Store basic registration data in session
        session([
            'basic_registration' => [
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]
        ]);

        return redirect()->route('auth.register.complete');
    }

    /**
     * Display the complete registration form.
     */
    public function showCompleteRegistration(): Response
    {
        $basicData = session('basic_registration');

        if (!$basicData) {
            return redirect()->route('auth.register');
        }

        return Inertia::render('Auth/CompleteRegistration', [
            'basicData' => $basicData,
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }

    /**
     * Handle complete user registration.
     */
    public function completeRegistration(Request $request)
    {
        $basicData = session('basic_registration');

        if (!$basicData) {
            return redirect()->route('auth.register');
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'apelido' => 'required|string|max:255',
            'celular' => 'required|string|max:20',
            'provincia' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with complete data
        $user = User::create([
            'name' => $request->nome . ' ' . $request->apelido,
            'username' => $basicData['username'],
            'email' => $basicData['email'],
            'password' => $basicData['password'],
            'phone' => $request->celular,
            'province' => $request->provincia,
            'district' => $request->distrito,
            'neighborhood' => $request->bairro,
            'street' => $request->rua,
        ]);

        // Handle document uploads if any
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $path = $document->store('user_documents', 'public');
                // You might want to create an Attachment model to store these
            }
        }

        // Assign default role to new users
        $user->assignRole('Utente');

        // Clear session data
        session()->forget('basic_registration');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Conta criada com sucesso! Bem-vindo!');
    }

    /**
     * Display the home page for authenticated users.
     */
    public function home(): Response
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        return Inertia::render('Home', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'created_at' => $user->created_at->format('d/m/Y'),
            ]
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
