<?php

namespace App\Traits;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Color\Rgb;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait TwoFactorAuthenticatable
{
    public function twoFactorAuth(): Google2FA
    {
        return new Google2FA();
    }

    public function initializeTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret'         => encrypt($this->twoFactorAuth()->generateSecretKey()),
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);

        $this->generateRecoveryCodes();
        $this->save();
    }

    public function getTwoFactorSecretAttribute(): ?string
    {
        try {
            if (empty($this->attributes['two_factor_secret'])) {
                return null;
            }

            $decrypted = decrypt($this->attributes['two_factor_secret']);

            return $decrypted;
        } catch (\Exception $e) {
            Log::error('Decrypt error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getTwoFactorQrCodeSvg(): string
    {
        if (!$this->two_factor_secret) {
            $this->initializeTwoFactorAuthentication();
        }

        try {
            $svg = (new Writer(
                new ImageRenderer(
                    new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                    new SvgImageBackEnd()
                )
            ))->writeString($this->getTwoFactorQrCodeUrl());

            return trim(substr($svg, strpos($svg, "\n") + 1));
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getTwoFactorQrCodeUrl(): string
    {
        try {
            $secret = $this->getTwoFactorSecretAttribute();

            if (!$secret) {
                Log::error('No secret available for QR code');
                return '';
            }

            return $this->twoFactorAuth()->getQRCodeUrl(
                config('app.name'),
                $this->email,
                $secret
            );
        } catch (\Exception $e) {
            Log::error('QR URL generation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }

    public function generateRecoveryCodes(): void
    {
        $this->two_factor_recovery_codes = encrypt(json_encode(Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        })->all()));

        $this->save();
    }

    public function getRecoveryCodes(): array
    {
        try {
            return !empty($this->two_factor_recovery_codes)
                ? json_decode(decrypt($this->two_factor_recovery_codes), true) ?? []
                : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        try {
            $secret = $this->getTwoFactorSecretAttribute();

            if (!$secret) {
                Log::error('Secret não encontrado durante verificação');
                return false;
            }

            return $this->twoFactorAuth()->verify(trim($code), $secret, config('auth.2fa.window', 1));
        } catch (\Exception $e) {
            Log::error('Erro na verificação 2FA:', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function enableTwoFactorAuth(): void
    {
        if (!$this->two_factor_secret) {
            $secret = $this->twoFactorAuth()->generateSecretKey();

            $this->forceFill([
                'two_factor_secret' => encrypt($secret),
                'two_factor_confirmed_at' => null,
            ]);
            $this->generateRecoveryCodes();
            $this->save();
        }
    }

    public function confirmTwoFactorAuth(string $code): bool
    {
        if ($this->verifyTwoFactorCode($code)) {
            $this->two_factor_confirmed = true;
            $this->save();
            return true;
        }
        return false;
    }
}
