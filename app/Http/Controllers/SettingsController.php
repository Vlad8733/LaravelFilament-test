<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('settings.index');
    }

    public function updateLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,ru,lv',
        ]);

        $user = Auth::user();
        $user->locale = $request->locale;
        $user->save();

        App::setLocale($request->locale);
        session(['locale' => $request->locale]);

        return back()->with('success', __('settings.language_updated'));
    }
}
