<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = $credentials['login'];
        $password = $credentials['password'];

        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $authCredentials = [
                'email' => $loginField,
                'password' => $password,
            ];
        } else {
            $authCredentials = [
                'matricula' => $loginField,
                'password' => $password,
            ];
        }

        $authCredentials['is_ativo'] = true;

        if (Auth::attempt($authCredentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Mark the session as just logged in to prevent middleware conflicts
            $request->session()->put('just_logged_in', true);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            if ($request->filled('branch_id')) {
                session(['user_branch_id' => $request->branch_id]);
            }

            // Regenerate session ID to prevent session fixation
            $request->session()->regenerate();

            if ($user->two_factor_confirmed_at) {
                return redirect()->route('two-factor.verificacao');
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        $errorMessage = $isEmail
            ? 'Email ou senha incorretos.'
            : 'Matrícula ou senha incorretos.';

        throw ValidationException::withMessages([
            'login' => $errorMessage,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
