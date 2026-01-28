<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

trait TwoFactorAuthenticatable
{
    protected function getGoogle2FA(): Google2FA
    {
        return app(Google2FA::class);
    }

    public function generateTwoFactorSecret(): string
    {
        return $this->getGoogle2FA()->generateSecretKey();
    }

    public function getTwoFactorQrCodeUrl(): string
    {
        return $this->getGoogle2FA()->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        return $this->getGoogle2FA()->verifyKey($this->two_factor_secret, $code);
    }

    public function enableTwoFactor(string $secret): void
    {
        $this->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ])->save();
    }

    public function disableTwoFactor(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    protected function generateRecoveryCodes(): array
    {
        return Collection::times(8, fn () => Str::random(10).'-'.Str::random(10))->all();
    }

    public function getRecoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }

    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getRecoveryCodes();

        if (! in_array($code, $codes)) {
            return false;
        }

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(
                array_values(array_diff($codes, [$code]))
            )),
        ])->save();

        return true;
    }

    public function regenerateRecoveryCodes(): array
    {
        $codes = $this->generateRecoveryCodes();

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }
}
