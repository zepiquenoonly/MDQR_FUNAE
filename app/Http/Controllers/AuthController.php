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
            'name' => 'required|string|max:255',
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

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role to new users
        $user->assignRole('Utente');

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
