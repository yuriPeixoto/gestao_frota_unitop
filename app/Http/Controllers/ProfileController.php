<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->has_password_updated = true;
        $user->password_updated_at = now();
        $user->save();

        return back()->with('status', 'password-updated');
    }

    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'max:1024']
            ]);

            if (!$request->hasFile('avatar')) {
                return redirect()
                    ->route('profile.edit')
                    ->with('error', 'Nenhum arquivo foi enviado.');
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            $request->user()->update(['avatar' => $path]);

            return redirect()
                ->route('profile.edit')
                ->with('status', 'avatar-updated');
        } catch (\Exception $e) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Erro ao fazer upload do avatar: ' . $e->getMessage());
        }
    }

    public function deleteAvatar(Request $request)
    {
        if ($request->user()->avatar) {
            Storage::disk('public')->delete($request->user()->avatar);
            $request->user()->update(['avatar' => null]);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        AuthServiceProvider::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function recordLogin(Request $request)
    {
        $request->user()->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }
}
