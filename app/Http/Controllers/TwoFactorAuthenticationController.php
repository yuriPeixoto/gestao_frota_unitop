<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function show(Request $request): View
    {
        return view('profile.two-factor-auth');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $secret = $this->google2fa->generateSecretKey();

        DB::beginTransaction();
        try {
            $user->forceFill([
                'two_factor_secret' => encrypt($secret),
                'two_factor_confirmed_at' => null,
            ])->save();

            // Generate initial recovery codes
            $recoveryCodes = collect(range(1, 8))->map(function () {
                return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
            })->toArray();

            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))
            ])->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Two-factor authentication enabled'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('2FA Store error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'O código informado é inválido.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with([
            'status' => 'two-factor-authentication-confirmed',
            'message' => 'Autenticação em dois fatores habilitada com sucesso!'
        ]);
    }

    public function showRecoveryCodes(Request $request): View
    {
        $recoveryCodes = json_decode(decrypt($request->user()->two_factor_recovery_codes), true);

        return view('profile.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $recoveryCodes = collect(range(1, 8))->map(function () {
            return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
        })->toArray();

        $request->user()->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))
        ])->save();

        return back()->with('success', 'Códigos de recuperação regenerados.');
    }

    public function destroy(Request $request)
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Autenticação em 2 fatores desabilitada.');
    }

    public function downloadCodes(Request $request)
    {
        $codes = $request->user()->getRecoveryCodes();
        $content = "Códigos de Recuperação - " . config('app.name') . "\n\n";
        $content .= implode("\n", $codes);

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="recovery-codes.txt"');
    }

    public function disable(Request $request)
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Autenticação em 2 fatores desabilitada com sucesso.');
    }

    public function verificacao()
    {
        return view('auth.two-factor-verificacao');
    }

    public function login(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();

        if ($user->verifyTwoFactorCode($request->code)) {
            session(['two_factor_authenticated' => true]);
            return redirect()->intended();
        }

        return back()->withErrors(['code' => 'O código informado é inválido.']);
    }
}
