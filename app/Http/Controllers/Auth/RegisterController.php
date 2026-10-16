<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Inscription publique réservée à l'installation : elle ne sert qu'à créer le tout premier
 * compte (administrateur système). Dès qu'un utilisateur existe, les comptes se créent
 * depuis le module Administration.
 */
class RegisterController extends Controller
{
    public function showRegisterForm(): View|RedirectResponse
    {
        if (User::exists()) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        if (User::exists()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        // Verrou pour que deux inscriptions simultanées ne créent pas deux administrateurs.
        $user = DB::transaction(function () use ($validated) {
            if (User::lockForUpdate()->exists()) {
                return null;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'admin',
                'scope' => 'dg',
            ]);

            if (Role::where('name', 'admin-systeme')->exists()) {
                $user->syncRoles(['admin-systeme']);
            }

            return $user;
        });

        if (!$user) {
            return redirect()->route('login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
