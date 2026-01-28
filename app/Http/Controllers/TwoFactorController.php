<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        return view('auth.two-factor.index', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'recoveryCodes' => $user->hasTwoFactorEnabled() ? $user->getRecoveryCodes() : [],
        ]);
    }

    public function setup(Request $request)
    {
        $user = auth()->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.index')
                ->with('info', __('Two-factor authentication is already enabled.'));
        }

        $secret = $user->generateTwoFactorSecret();

        $request->session()->put('2fa_secret', $secret);

        $google2fa = app(Google2FA::class);
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrCode = $this->generateQrCodeSvg($qrCodeUrl);

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => __('The password is incorrect.')]);
        }

        $secret = $request->session()->get('2fa_secret');

        if (! $secret) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => __('Session expired. Please try again.')]);
        }

        $google2fa = app(Google2FA::class);

        if (! $google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => __('The code is invalid.')]);
        }

        $user->enableTwoFactor($secret);

        $request->session()->forget('2fa_secret');

        return redirect()->route('two-factor.index')
            ->with('success', __('Two-factor authentication has been enabled.'));
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => __('The password is incorrect.')]);
        }

        $user->disableTwoFactor();

        return redirect()->route('two-factor.index')
            ->with('success', __('Two-factor authentication has been disabled.'));
    }

    public function regenerateCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => __('The password is incorrect.')]);
        }

        $user->regenerateRecoveryCodes();

        return redirect()->route('two-factor.index')
            ->with('success', __('Recovery codes have been regenerated.'));
    }

    protected function generateQrCodeSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd;
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            $renderer
        );

        $writer = new \BaconQrCode\Writer($renderer);

        return $writer->writeString($url);
    }
}
